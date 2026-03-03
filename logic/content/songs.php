<?php
/**
 * Songs Content Logic
 * Functions for retrieving and managing song data from the database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get database connection
 */
function getSongsDb()
{
    $database = new Database();
    return $database->getConnection();
}

/**
 * Get all songs with artist information
 */
function getAllSongs($limit = 50, $offset = 0)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name, a.image_url as artist_image
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        ORDER BY s.play_count DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get a single song by ID with artist information
 */
function getSongById($songId)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name, a.image_url as artist_image, al.title as album_title
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        LEFT JOIN albums al ON s.album_id = al.id
        WHERE s.id = :id
    ");
    $stmt->bindValue(':id', (int) $songId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get songs by genre
 */
function getSongsByGenre($genre, $limit = 20)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE s.genre = :genre
        ORDER BY s.play_count DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':genre', $genre, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get songs by artist ID
 */
function getSongsByArtist($artistId, $limit = 20)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE sa.artist_id = :artist_id
        ORDER BY s.play_count DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':artist_id', (int) $artistId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get songs by album ID
 */
function getSongsByAlbum($albumId)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE s.album_id = :album_id
        ORDER BY s.id ASC
    ");
    $stmt->bindValue(':album_id', (int) $albumId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get top songs by play count
 */
function getTopSongs($limit = 10)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        ORDER BY s.play_count DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get recently played songs for a user
 */
function getRecentlyPlayedSongs($userId, $limit = 10)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT DISTINCT s.*, a.name as artist_name, ph.played_at
        FROM play_history ph
        JOIN songs s ON ph.song_id = s.id
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE ph.user_id = :user_id
        ORDER BY ph.played_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Search songs by title
 */
function searchSongs($query, $limit = 20)
{
    $db = getSongsDb();
    $stmt = $db->prepare("
        SELECT s.*, a.name as artist_name
        FROM songs s
        LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
        LEFT JOIN artists a ON sa.artist_id = a.id
        WHERE s.title LIKE :query OR a.name LIKE :query
        ORDER BY s.play_count DESC
        LIMIT :limit
    ");
    $searchQuery = '%' . $query . '%';
    $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Increment play count for a song
 */
function incrementPlayCount($songId)
{
    $db = getSongsDb();
    $stmt = $db->prepare("UPDATE songs SET play_count = play_count + 1 WHERE id = :id");
    $stmt->bindValue(':id', (int) $songId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Record a play in play history
 */
function recordPlay($userId, $songId)
{
    $db = getSongsDb();
    $stmt = $db->prepare("INSERT INTO play_history (user_id, song_id) VALUES (:user_id, :song_id)");
    $stmt->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
    $stmt->bindValue(':song_id', (int) $songId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Get total song count
 */
function getSongCount()
{
    $db = getSongsDb();
    $stmt = $db->query("SELECT COUNT(*) as count FROM songs");
    return $stmt->fetch()['count'];
}

/**
 * Get all unique genres
 */
function getAllGenres()
{
    $db = getSongsDb();
    $stmt = $db->query("SELECT DISTINCT genre FROM songs WHERE genre IS NOT NULL ORDER BY genre");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Format song duration from seconds to MM:SS
 */
function formatDuration($seconds)
{
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%d:%02d', $minutes, $secs);
}

/**
 * Render a song card HTML
 */
function renderSongCard($song, $index = null)
{
    $title = htmlspecialchars($song['title']);
    $artist = htmlspecialchars($song['artist_name'] ?? 'Unknown Artist');
    $cover = htmlspecialchars($song['cover_image'] ?? 'assets/images/default-cover.png');
    $audio = htmlspecialchars($song['audio_file_path']);
    $duration = formatDuration($song['duration'] ?? 0);
    $songId = (int) $song['id'];

    $indexHtml = $index !== null ? '<span class="song-index">' . ($index + 1) . '</span>' : '';

    return <<<HTML
    <div class="song-item"
         data-song-id="{$songId}"
         data-song-title="{$title}"
         data-song-artist="{$artist}"
         data-song-audio="{$audio}"
         data-song-cover="{$cover}"
         data-song-duration="{$song['duration']}">
        {$indexHtml}
        <div class="songimg">
            <img src="{$cover}" alt="{$title}">
            <div class="songdetails">
                <h3>{$title}</h3>
                <p>{$artist}</p>
            </div>
        </div>
        <div class="song-actions">
            <button class="favorite-btn" data-song-id="{$songId}" title="Favorite">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            </button>
            <span class="songduration">{$duration}</span>
        </div>
    </div>
HTML;
}

/**
 * Render a song list item for library/favorites
 */
function renderSongListItem($song, $index = null)
{
    $title = htmlspecialchars($song['title']);
    $artist = htmlspecialchars($song['artist_name'] ?? 'Unknown Artist');
    $cover = htmlspecialchars($song['cover_image'] ?? 'assets/images/default-cover.png');
    $audio = htmlspecialchars($song['audio_file_path']);
    $duration = formatDuration($song['duration'] ?? 0);
    $songId = (int) $song['id'];
    $albumTitle = htmlspecialchars($song['album_title'] ?? $song['title']);

    $indexDisplay = $index !== null ? ($index + 1) : '';

    return <<<HTML
    <div class="favorite-song-item song-item"
         data-song-id="{$songId}"
         data-song-title="{$title}"
         data-song-artist="{$artist}"
         data-song-audio="{$audio}"
         data-song-cover="{$cover}"
         data-song-duration="{$song['duration']}">
        <span class="song-number">{$indexDisplay}</span>
        <img src="{$cover}" alt="{$title}" class="song-thumbnail">
        <div class="song-info">
            <span class="song-title">{$title}</span>
            <span class="song-artist">{$artist}</span>
        </div>
        <span class="song-album">{$albumTitle}</span>
        <div class="song-actions-right">
            <button class="favorite-btn" data-song-id="{$songId}" title="Favorite">
                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="heart-icon"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            </button>
            <span class="song-duration">{$duration}</span>
        </div>
    </div>
HTML;
}

/**
 * Get songs as JSON for JavaScript
 */
function getSongsAsJson($songs)
{
    $result = [];
    foreach ($songs as $song) {
        $result[] = [
            'id' => (int) $song['id'],
            'title' => $song['title'],
            'artist' => $song['artist_name'] ?? 'Unknown Artist',
            'audioUrl' => $song['audio_file_path'],
            'coverUrl' => $song['cover_image'] ?? 'assets/images/default-cover.png',
            'duration' => (int) ($song['duration'] ?? 0),
            'genre' => $song['genre'] ?? ''
        ];
    }
    return json_encode($result);
}
