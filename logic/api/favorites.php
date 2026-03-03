<?php
/**
 * Favorites API Endpoint
 * Handles favorite-related API requests
 */


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../content/favorites.php';
require_once __DIR__ . '/../utils/auth.php';

// Most favorite actions require authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$userId = getCurrentUser()['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $type = $_GET['type'] ?? null;
            $favorites = getUserFavorites($userId, $type);
            echo json_encode(['success' => true, 'data' => $favorites]);
            break;

        case 'songs':
            $songs = getFavoriteSongs($userId);
            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'playlists':
            $playlists = getFavoritePlaylists($userId);
            echo json_encode(['success' => true, 'data' => $playlists]);
            break;

        case 'articles':
            $articles = getFavoriteArticles($userId);
            echo json_encode(['success' => true, 'data' => $articles]);
            break;

        case 'videos':
            $videos = getFavoriteVideos($userId);
            echo json_encode(['success' => true, 'data' => $videos]);
            break;

        case 'add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $type = $input['type'] ?? '';
            $itemId = isset($input['item_id']) ? (int) $input['item_id'] : 0;

            if (!$type || !$itemId) {
                throw new Exception('Type and item_id required');
            }

            $validTypes = ['song', 'playlist', 'article', 'video'];
            if (!in_array($type, $validTypes)) {
                throw new Exception('Invalid type. Must be: ' . implode(', ', $validTypes));
            }

            addFavorite($userId, $type, $itemId);
            echo json_encode(['success' => true, 'message' => 'Added to favorites', 'is_favorite' => true]);
            break;

        case 'remove':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $type = $input['type'] ?? '';
            $itemId = isset($input['item_id']) ? (int) $input['item_id'] : 0;

            if (!$type || !$itemId) {
                throw new Exception('Type and item_id required');
            }

            removeFavorite($userId, $type, $itemId);
            echo json_encode(['success' => true, 'message' => 'Removed from favorites', 'is_favorite' => false]);
            break;

        case 'toggle':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $type = $input['type'] ?? '';
            $itemId = isset($input['item_id']) ? (int) $input['item_id'] : 0;

            if (!$type || !$itemId) {
                throw new Exception('Type and item_id required');
            }

            $validTypes = ['song', 'playlist', 'article', 'video'];
            if (!in_array($type, $validTypes)) {
                throw new Exception('Invalid type. Must be: ' . implode(', ', $validTypes));
            }

            $isFavorite = toggleFavorite($userId, $type, $itemId);
            echo json_encode([
                'success' => true,
                'is_favorite' => $isFavorite,
                'message' => $isFavorite ? 'Added to favorites' : 'Removed from favorites'
            ]);
            break;

        case 'check':
            $type = $_GET['type'] ?? '';
            $itemId = isset($_GET['item_id']) ? (int) $_GET['item_id'] : 0;

            if (!$type || !$itemId) {
                throw new Exception('Type and item_id required');
            }

            $isFavorite = isFavorite($userId, $type, $itemId);
            echo json_encode(['success' => true, 'is_favorite' => $isFavorite]);
            break;

        case 'count':
            $type = $_GET['type'] ?? null;
            $count = getFavoriteCount($userId, $type);
            echo json_encode(['success' => true, 'count' => $count]);
            break;

        case 'stats':
            $stats = [
                'songs' => getFavoriteCount($userId, 'song'),
                'playlists' => getFavoriteCount($userId, 'playlist'),
                'articles' => getFavoriteCount($userId, 'article'),
                'videos' => getFavoriteCount($userId, 'video'),
                'total_duration' => getFavoriteSongsTotalDuration($userId)
            ];
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
