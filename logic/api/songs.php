<?php
/**
 * Songs API Endpoint
 * Handles song-related API requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../content/songs.php';
require_once __DIR__ . '/../utils/auth.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $songs = getAllSongs($limit, $offset);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'get':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                throw new Exception('Song ID required');
            }
            $song = getSongById($id);
            if (!$song) {
                throw new Exception('Song not found');
            }
            echo json_encode(['success' => true, 'data' => $song]);
            break;

        case 'top':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $songs = getTopSongs($limit);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'genre':
            $genre = $_GET['genre'] ?? '';
            if (!$genre) {
                throw new Exception('Genre required');
            }
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $songs = getSongsByGenre($genre, $limit);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'artist':
            $artistId = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;
            if (!$artistId) {
                throw new Exception('Artist ID required');
            }
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $songs = getSongsByArtist($artistId, $limit);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'album':
            $albumId = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;
            if (!$albumId) {
                throw new Exception('Album ID required');
            }
            $songs = getSongsByAlbum($albumId);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'search':
            $query = $_GET['q'] ?? '';
            if (!$query) {
                throw new Exception('Search query required');
            }
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $songs = searchSongs($query, $limit);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'genres':
            $genres = getAllGenres();
            echo json_encode(['success' => true, 'data' => $genres]);
            break;

        case 'play':
            // Record a play (POST only)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;
            if (!$songId) {
                throw new Exception('Song ID required');
            }

            // Increment play count
            incrementPlayCount($songId);

            // Record in play history if user is logged in
            if (isAuthenticated()) {
                $userId = getCurrentUser()['id'];
                recordPlay($userId, $songId);
            }

            echo json_encode(['success' => true, 'message' => 'Play recorded']);
            break;

        case 'recent':
            // Get recently played for current user
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }
            $userId = getCurrentUser()['id'];
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $songs = getRecentlyPlayedSongs($userId, $limit);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
