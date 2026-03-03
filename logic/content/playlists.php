<?php
/**
 * Playlists Content Logic
 * Functions for managing playlists and playlist songs
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function getPlaylistsDb() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Get user's playlists
 */
function getUserPlaylists($userId) {
    $db = getPlaylistsDb();
    $stmt = $db->prepare("
        SELECT p.*, COUNT(ps.song_id) as song_count
        FROM playlists p
        LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
        WHERE p.user_id = :user_id
        GROUP BY p.id
        ORDER BY p.updated_at DESC
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get playlist by ID
 */
function getPlaylistById($playlistId) {
    $db = getPlaylistsDb();
    $stmt = $db->prepare("
        SELECT p.*, u.username as owner_name, COUNT(ps.song_id) as song_count
        FROM playlists p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
        WHERE p.id = :id
        GROUP BY p.id
    ");
    $stmt->bindValue(':id', (int)$playlistId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get playlist with all its songs
 */
function getPlaylistWithSongs($playlistId) {
    $playlist = getPlaylistById($playlistId);
    if (!$playlist) {
        return null;
    }

    $db = getPlaylistsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name, ps.position
        FROM playlist_songs ps
        JOIN songs s ON ps.song_id = s.id
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE ps.playlist_id = :playlist_id
        ORDER BY ps.position ASC
    ");
    $stmt->bindValue(':playlist_id', (int)$playlistId, PDO::PARAM_INT);
    $stmt->execute();

    $playlist['songs'] = $stmt->fetchAll();

    // Calculate total duration
    $totalDuration = 0;
    foreach ($playlist['songs'] as $song) {
        $totalDuration += $song['duration'] ?? 0;
    }
    $playlist['total_duration'] = $totalDuration;

    return $playlist;
}

/**
 * Get public playlists
 */
function getPublicPlaylists($limit = 20) {
    $db = getPlaylistsDb();
    $stmt = $db->prepare("
        SELECT p.*, u.username as owner_name, COUNT(ps.song_id) as song_count
        FROM playlists p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
        WHERE p.is_public = 1
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Create a new playlist
 */
function createPlaylist($userId, $name, $description = '', $coverImage = null, $isPublic = true) {
    $db = getPlaylistsDb();
    $stmt = $db->prepare("
        INSERT INTO playlists (user_id, name, description, cover_image, is_public)
        VALUES (:user_id, :name, :description, :cover_image, :is_public)
    ");
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':cover_image', $coverImage, PDO::PARAM_STR);
    $stmt->bindValue(':is_public', $isPublic ? 1 : 0, PDO::PARAM_INT);
    $stmt->execute();

    return $db->lastInsertId();
}

/**
 * Add song to playlist
 */
function addSongToPlaylist($playlistId, $songId, $position = null) {
    $db = getPlaylistsDb();

    // If no position specified, add at the end
    if ($position === null) {
        $stmt = $db->prepare("SELECT MAX(position) as max_pos FROM playlist_songs WHERE playlist_id = :playlist_id");
        $stmt->bindValue(':playlist_id', (int)$playlistId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $position = ($result['max_pos'] ?? 0) + 1;
    }

    $stmt = $db->prepare("
        INSERT INTO playlist_songs (playlist_id, song_id, position)
        VALUES (:playlist_id, :song_id, :position)
        ON DUPLICATE KEY UPDATE position = :position
    ");
    $stmt->bindValue(':playlist_id', (int)$playlistId, PDO::PARAM_INT);
    $stmt->bindValue(':song_id', (int)$songId, PDO::PARAM_INT);
    $stmt->bindValue(':position', (int)$position, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Remove song from playlist
 */
function removeSongFromPlaylist($playlistId, $songId) {
    $db = getPlaylistsDb();
    $stmt = $db->prepare("DELETE FROM playlist_songs WHERE playlist_id = :playlist_id AND song_id = :song_id");
    $stmt->bindValue(':playlist_id', (int)$playlistId, PDO::PARAM_INT);
    $stmt->bindValue(':song_id', (int)$songId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Delete playlist (only if user owns it)
 */
function deletePlaylist($playlistId, $userId) {
    $db = getPlaylistsDb();
    $stmt = $db->prepare("DELETE FROM playlists WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', (int)$playlistId, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Update playlist details
 */
function updatePlaylist($playlistId, $userId, $data) {
    $db = getPlaylistsDb();

    $updates = [];
    $params = [':id' => (int)$playlistId, ':user_id' => (int)$userId];

    if (isset($data['name'])) {
        $updates[] = "name = :name";
        $params[':name'] = $data['name'];
    }
    if (isset($data['description'])) {
        $updates[] = "description = :description";
        $params[':description'] = $data['description'];
    }
    if (isset($data['cover_image'])) {
        $updates[] = "cover_image = :cover_image";
        $params[':cover_image'] = $data['cover_image'];
    }
    if (isset($data['is_public'])) {
        $updates[] = "is_public = :is_public";
        $params[':is_public'] = $data['is_public'] ? 1 : 0;
    }

    if (empty($updates)) {
        return false;
    }

    $sql = "UPDATE playlists SET " . implode(', ', $updates) . " WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    return $stmt->execute();
}

/**
 * Format playlist duration from seconds
 */
function formatPlaylistDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($hours > 0) {
        return sprintf('%d hr %d min', $hours, $minutes);
    }
    return sprintf('%d min', $minutes);
}

/**
 * Render playlist card HTML
 */
function renderPlaylistCard($playlist) {
    $name = htmlspecialchars($playlist['name']);
    $cover = htmlspecialchars($playlist['cover_image'] ?? 'assets/images/default-playlist.png');
    $songCount = (int)($playlist['song_count'] ?? 0);
    $playlistId = (int)$playlist['id'];
    $owner = htmlspecialchars($playlist['owner_name'] ?? 'Unknown');

    return <<<HTML
    <div class="playlist-card" data-playlist-id="{$playlistId}">
        <div class="playlist-cover">
            <img src="{$cover}" alt="{$name}">
            <div class="playlist-play-overlay">
                <svg class="lucide lucide-play" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="6 3 20 12 6 21 6 3"/>
                </svg>
            </div>
        </div>
        <h4 class="playlist-name">{$name}</h4>
        <p class="playlist-info">{$songCount} songs</p>
    </div>
HTML;
}

/**
 * Render library playlist card (smaller variant)
 */
function renderLibraryPlaylistCard($playlist) {
    $name = htmlspecialchars($playlist['name']);
    $cover = htmlspecialchars($playlist['cover_image'] ?? 'assets/images/default-playlist.png');
    $songCount = (int)($playlist['song_count'] ?? 0);
    $playlistId = (int)$playlist['id'];

    return <<<HTML
    <div class="library-playlist-card" data-playlist-id="{$playlistId}">
        <img src="{$cover}" alt="{$name}" class="library-playlist-cover">
        <h4>{$name}</h4>
        <p>{$songCount} songs</p>
    </div>
HTML;
}
