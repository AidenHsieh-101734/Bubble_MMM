<?php
/**
 * Artists Content Logic
 * Functions for retrieving artist data from the database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function getArtistsDb() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Get all artists
 */
function getAllArtists($limit = 50) {
    $db = getArtistsDb();
    $stmt = $db->prepare("
        SELECT a.*, COUNT(DISTINCT sa.song_id) as song_count
        FROM artists a
        LEFT JOIN song_artists sa ON a.id = sa.artist_id
        GROUP BY a.id
        ORDER BY song_count DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get artist by ID
 */
function getArtistById($artistId) {
    $db = getArtistsDb();
    $stmt = $db->prepare("SELECT * FROM artists WHERE id = :id");
    $stmt->bindValue(':id', (int)$artistId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get top artists (by total play count of their songs)
 */
function getTopArtists($limit = 10) {
    $db = getArtistsDb();
    $stmt = $db->prepare("
        SELECT a.*, SUM(s.play_count) as total_plays, COUNT(DISTINCT s.id) as song_count
        FROM artists a
        JOIN song_artists sa ON a.id = sa.artist_id
        JOIN songs s ON sa.song_id = s.id
        GROUP BY a.id
        ORDER BY total_plays DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get artists by genre
 */
function getArtistsByGenre($genre, $limit = 10) {
    $db = getArtistsDb();
    $stmt = $db->prepare("
        SELECT * FROM artists
        WHERE genre = :genre
        ORDER BY name ASC
        LIMIT :limit
    ");
    $stmt->bindValue(':genre', $genre, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Search artists by name
 */
function searchArtists($query, $limit = 20) {
    $db = getArtistsDb();
    $stmt = $db->prepare("
        SELECT * FROM artists
        WHERE name LIKE :query
        ORDER BY name ASC
        LIMIT :limit
    ");
    $searchQuery = '%' . $query . '%';
    $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get artist with their songs
 */
function getArtistWithSongs($artistId) {
    $artist = getArtistById($artistId);
    if (!$artist) {
        return null;
    }

    $db = getArtistsDb();
    $stmt = $db->prepare("
        SELECT s.*, al.title as album_title
        FROM songs s
        JOIN song_artists sa ON s.id = sa.song_id
        LEFT JOIN albums al ON s.album_id = al.id
        WHERE sa.artist_id = :artist_id
        ORDER BY s.play_count DESC
    ");
    $stmt->bindValue(':artist_id', (int)$artistId, PDO::PARAM_INT);
    $stmt->execute();

    $artist['songs'] = $stmt->fetchAll();
    return $artist;
}

/**
 * Get artist count
 */
function getArtistCount() {
    $db = getArtistsDb();
    $stmt = $db->query("SELECT COUNT(*) as count FROM artists");
    return $stmt->fetch()['count'];
}

/**
 * Render artist card HTML
 */
function renderArtistCard($artist) {
    $name = htmlspecialchars($artist['name']);
    $image = htmlspecialchars($artist['image_url'] ?? 'assets/images/default-artist.png');
    $genre = htmlspecialchars($artist['genre'] ?? '');
    $artistId = (int)$artist['id'];

    return <<<HTML
    <div class="artist-item" data-artist-id="{$artistId}">
        <div class="artist-image">
            <img src="{$image}" alt="{$name}">
        </div>
        <h4 class="artist-name">{$name}</h4>
        <p class="artist-genre">{$genre}</p>
    </div>
HTML;
}

/**
 * Render artist thumbnail (for sidebar/smaller displays)
 */
function renderArtistThumbnail($artist) {
    $name = htmlspecialchars($artist['name']);
    $image = htmlspecialchars($artist['image_url'] ?? 'assets/images/default-artist.png');
    $artistId = (int)$artist['id'];

    return <<<HTML
    <div class="artist-thumbnail" data-artist-id="{$artistId}">
        <img src="{$image}" alt="{$name}">
        <span>{$name}</span>
    </div>
HTML;
}
