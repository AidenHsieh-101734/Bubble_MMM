<?php
/**
 * Favorites Content Logic
 * Functions for managing user favorites (polymorphic)
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function getFavoritesDb() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Get all user favorites
 */
function getUserFavorites($userId, $type = null) {
    $db = getFavoritesDb();

    $sql = "SELECT * FROM user_favorites WHERE user_id = :user_id";
    if ($type) {
        $sql .= " AND favoritable_type = :type";
    }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    if ($type) {
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get user's favorite songs with full song data
 */
function getFavoriteSongs($userId) {
    $db = getFavoritesDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name, uf.created_at as favorited_at
        FROM user_favorites uf
        JOIN songs s ON uf.favoritable_id = s.id
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE uf.user_id = :user_id AND uf.favoritable_type = 'song'
        ORDER BY uf.created_at DESC
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get user's favorite playlists with full data
 */
function getFavoritePlaylists($userId) {
    $db = getFavoritesDb();
    $stmt = $db->prepare("
        SELECT p.*, u.username as owner_name, COUNT(ps.song_id) as song_count, uf.created_at as favorited_at
        FROM user_favorites uf
        JOIN playlists p ON uf.favoritable_id = p.id
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
        WHERE uf.user_id = :user_id AND uf.favoritable_type = 'playlist'
        GROUP BY p.id
        ORDER BY uf.created_at DESC
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get user's favorite articles with full data
 */
function getFavoriteArticles($userId) {
    $db = getFavoritesDb();
    $stmt = $db->prepare("
        SELECT a.*, u.full_name as author_name, uf.created_at as favorited_at
        FROM user_favorites uf
        JOIN articles a ON uf.favoritable_id = a.id
        LEFT JOIN users u ON a.author_id = u.id
        WHERE uf.user_id = :user_id AND uf.favoritable_type = 'article'
        ORDER BY uf.created_at DESC
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get user's favorite videos with full data
 */
function getFavoriteVideos($userId) {
    $db = getFavoritesDb();
    $stmt = $db->prepare("
        SELECT v.*, uf.created_at as favorited_at
        FROM user_favorites uf
        JOIN videos v ON uf.favoritable_id = v.id
        WHERE uf.user_id = :user_id AND uf.favoritable_type = 'video'
        ORDER BY uf.created_at DESC
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Add item to favorites
 */
function addFavorite($userId, $type, $itemId) {
    $db = getFavoritesDb();

    // Check if already favorited
    if (isFavorite($userId, $type, $itemId)) {
        return true;
    }

    $stmt = $db->prepare("
        INSERT INTO user_favorites (user_id, favoritable_type, favoritable_id)
        VALUES (:user_id, :type, :item_id)
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':item_id', (int)$itemId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Remove item from favorites
 */
function removeFavorite($userId, $type, $itemId) {
    $db = getFavoritesDb();
    $stmt = $db->prepare("
        DELETE FROM user_favorites
        WHERE user_id = :user_id AND favoritable_type = :type AND favoritable_id = :item_id
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':item_id', (int)$itemId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Toggle favorite status
 */
function toggleFavorite($userId, $type, $itemId) {
    if (isFavorite($userId, $type, $itemId)) {
        removeFavorite($userId, $type, $itemId);
        return false; // Now not favorited
    } else {
        addFavorite($userId, $type, $itemId);
        return true; // Now favorited
    }
}

/**
 * Check if item is favorited
 */
function isFavorite($userId, $type, $itemId) {
    $db = getFavoritesDb();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count FROM user_favorites
        WHERE user_id = :user_id AND favoritable_type = :type AND favoritable_id = :item_id
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    $stmt->bindValue(':item_id', (int)$itemId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch()['count'] > 0;
}

/**
 * Get favorite count for a user
 */
function getFavoriteCount($userId, $type = null) {
    $db = getFavoritesDb();

    $sql = "SELECT COUNT(*) as count FROM user_favorites WHERE user_id = :user_id";
    if ($type) {
        $sql .= " AND favoritable_type = :type";
    }

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    if ($type) {
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetch()['count'];
}

/**
 * Get total duration of favorite songs
 */
function getFavoriteSongsTotalDuration($userId) {
    $db = getFavoritesDb();
    $stmt = $db->prepare("
        SELECT SUM(s.duration) as total
        FROM user_favorites uf
        JOIN songs s ON uf.favoritable_id = s.id
        WHERE uf.user_id = :user_id AND uf.favoritable_type = 'song'
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch()['total'] ?? 0;
}

/**
 * Format duration for display
 */
function formatFavoritesDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($hours > 0) {
        return sprintf('%d hr %d min', $hours, $minutes);
    }
    return sprintf('%d min', $minutes);
}
