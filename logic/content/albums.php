<?php
/**
 * Albums Content Logic
 * Functions for retrieving album data from the database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function getAlbumsDb() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Get all albums with artist info
 */
function getAllAlbums($limit = 50) {
    $db = getAlbumsDb();
    $stmt = $db->prepare("
        SELECT al.*, a.name as artist_name, COUNT(s.id) as song_count
        FROM albums al
        LEFT JOIN artists a ON al.artist_id = a.id
        LEFT JOIN songs s ON al.id = s.album_id
        GROUP BY al.id
        ORDER BY al.release_date DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get album by ID
 */
function getAlbumById($albumId) {
    $db = getAlbumsDb();
    $stmt = $db->prepare("
        SELECT al.*, a.name as artist_name
        FROM albums al
        LEFT JOIN artists a ON al.artist_id = a.id
        WHERE al.id = :id
    ");
    $stmt->bindValue(':id', (int)$albumId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get top albums (by combined play count of songs)
 */
function getTopAlbums($limit = 10) {
    $db = getAlbumsDb();
    $stmt = $db->prepare("
        SELECT al.*, a.name as artist_name, SUM(s.play_count) as total_plays
        FROM albums al
        LEFT JOIN artists a ON al.artist_id = a.id
        LEFT JOIN songs s ON al.id = s.album_id
        GROUP BY al.id
        ORDER BY total_plays DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get albums by artist
 */
function getAlbumsByArtist($artistId) {
    $db = getAlbumsDb();
    $stmt = $db->prepare("
        SELECT al.*, COUNT(s.id) as song_count
        FROM albums al
        LEFT JOIN songs s ON al.id = s.album_id
        WHERE al.artist_id = :artist_id
        GROUP BY al.id
        ORDER BY al.release_date DESC
    ");
    $stmt->bindValue(':artist_id', (int)$artistId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get albums by genre
 */
function getAlbumsByGenre($genre, $limit = 10) {
    $db = getAlbumsDb();
    $stmt = $db->prepare("
        SELECT al.*, a.name as artist_name
        FROM albums al
        LEFT JOIN artists a ON al.artist_id = a.id
        WHERE al.genre = :genre
        ORDER BY al.release_date DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':genre', $genre, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get album with all its songs
 */
function getAlbumWithSongs($albumId) {
    $album = getAlbumById($albumId);
    if (!$album) {
        return null;
    }

    $db = getAlbumsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE s.album_id = :album_id
        ORDER BY s.id ASC
    ");
    $stmt->bindValue(':album_id', (int)$albumId, PDO::PARAM_INT);
    $stmt->execute();

    $album['songs'] = $stmt->fetchAll();
    return $album;
}

/**
 * Search albums by title
 */
function searchAlbums($query, $limit = 20) {
    $db = getAlbumsDb();
    $stmt = $db->prepare("
        SELECT al.*, a.name as artist_name
        FROM albums al
        LEFT JOIN artists a ON al.artist_id = a.id
        WHERE al.title LIKE :query
        ORDER BY al.release_date DESC
        LIMIT :limit
    ");
    $searchQuery = '%' . $query . '%';
    $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get album count
 */
function getAlbumCount() {
    $db = getAlbumsDb();
    $stmt = $db->query("SELECT COUNT(*) as count FROM albums");
    return $stmt->fetch()['count'];
}

/**
 * Render album card HTML
 */
function renderAlbumCard($album) {
    $title = htmlspecialchars($album['title']);
    $artist = htmlspecialchars($album['artist_name'] ?? 'Unknown Artist');
    $cover = htmlspecialchars($album['cover_image'] ?? 'assets/images/default-album.png');
    $albumId = (int)$album['id'];
    $releaseYear = $album['release_date'] ? date('Y', strtotime($album['release_date'])) : '';

    return <<<HTML
    <div class="album-item" data-album-id="{$albumId}">
        <div class="album-cover">
            <img src="{$cover}" alt="{$title}">
            <div class="album-play-overlay">
                <svg class="lucide lucide-play" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="6 3 20 12 6 21 6 3"/>
                </svg>
            </div>
        </div>
        <h4 class="album-title">{$title}</h4>
        <p class="album-artist">{$artist}</p>
    </div>
HTML;
}

/**
 * Format album release date
 */
function formatAlbumDate($dateString) {
    if (!$dateString) return '';
    return date('M j, Y', strtotime($dateString));
}
