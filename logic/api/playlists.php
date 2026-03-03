<?php
/**
 * Playlists API Endpoint
 * Handles playlist-related API requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../content/playlists.php';
require_once __DIR__ . '/../utils/auth.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
        case 'public':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $playlists = getPublicPlaylists($limit);
            echo json_encode(['success' => true, 'data' => $playlists]);
            break;

        case 'user':
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }
            $userId = getCurrentUser()['id'];
            $playlists = getUserPlaylists($userId);
            echo json_encode(['success' => true, 'data' => $playlists]);
            break;

        case 'get':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                throw new Exception('Playlist ID required');
            }
            $playlist = getPlaylistWithSongs($id);
            if (!$playlist) {
                throw new Exception('Playlist not found');
            }
            echo json_encode(['success' => true, 'data' => $playlist]);
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = getCurrentUser()['id'];
            $name = $input['name'] ?? '';
            $description = $input['description'] ?? '';
            $coverImage = $input['cover_image'] ?? null;
            $isPublic = $input['is_public'] ?? true;

            if (!$name) {
                throw new Exception('Playlist name required');
            }

            $playlistId = createPlaylist($userId, $name, $description, $coverImage, $isPublic);
            echo json_encode(['success' => true, 'data' => ['id' => $playlistId]]);
            break;

        case 'add_song':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $playlistId = isset($input['playlist_id']) ? (int)$input['playlist_id'] : 0;
            $songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;

            if (!$playlistId || !$songId) {
                throw new Exception('Playlist ID and Song ID required');
            }

            // Verify user owns the playlist
            $playlist = getPlaylistById($playlistId);
            if (!$playlist || $playlist['user_id'] != getCurrentUser()['id']) {
                throw new Exception('Access denied');
            }

            addSongToPlaylist($playlistId, $songId);
            echo json_encode(['success' => true, 'message' => 'Song added to playlist']);
            break;

        case 'remove_song':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $playlistId = isset($input['playlist_id']) ? (int)$input['playlist_id'] : 0;
            $songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;

            if (!$playlistId || !$songId) {
                throw new Exception('Playlist ID and Song ID required');
            }

            // Verify user owns the playlist
            $playlist = getPlaylistById($playlistId);
            if (!$playlist || $playlist['user_id'] != getCurrentUser()['id']) {
                throw new Exception('Access denied');
            }

            removeSongFromPlaylist($playlistId, $songId);
            echo json_encode(['success' => true, 'message' => 'Song removed from playlist']);
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                throw new Exception('POST or DELETE method required');
            }
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $playlistId = isset($input['playlist_id']) ? (int)$input['playlist_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

            if (!$playlistId) {
                throw new Exception('Playlist ID required');
            }

            $userId = getCurrentUser()['id'];
            deletePlaylist($playlistId, $userId);
            echo json_encode(['success' => true, 'message' => 'Playlist deleted']);
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $playlistId = isset($input['playlist_id']) ? (int)$input['playlist_id'] : 0;

            if (!$playlistId) {
                throw new Exception('Playlist ID required');
            }

            $userId = getCurrentUser()['id'];
            updatePlaylist($playlistId, $userId, $input);
            echo json_encode(['success' => true, 'message' => 'Playlist updated']);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
