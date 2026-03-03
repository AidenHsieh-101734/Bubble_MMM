<?php
/**
 * Play History API Endpoint
 * Handles play history tracking and retrieval
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../content/songs.php';
require_once __DIR__ . '/../utils/auth.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'recent';

try {
    switch ($action) {
        case 'record':
            // Record a play in history (POST only)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;

            if (!$songId) {
                throw new Exception('Song ID required');
            }

            // Increment play count (works for all users)
            incrementPlayCount($songId);

            // Record in play history only if user is logged in
            if (isAuthenticated()) {
                $userId = getCurrentUser()['id'];
                recordPlay($userId, $songId);
            }

            echo json_encode(['success' => true, 'message' => 'Play recorded']);
            break;

        case 'recent':
            // Get recently played songs for current user
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }

            $userId = getCurrentUser()['id'];
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $songs = getRecentlyPlayedSongs($userId, $limit);

            echo json_encode(['success' => true, 'data' => $songs]);
            break;

        case 'clear':
            // Clear play history for current user (POST only)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST method required');
            }

            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }

            $userId = getCurrentUser()['id'];
            $database = new Database();
            $db = $database->getConnection();

            $stmt = $db->prepare("DELETE FROM play_history WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Play history cleared']);
            break;

        case 'stats':
            // Get listening stats for current user
            if (!isAuthenticated()) {
                throw new Exception('Authentication required');
            }

            $userId = getCurrentUser()['id'];
            $database = new Database();
            $db = $database->getConnection();

            // Total plays
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM play_history WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $totalPlays = $stmt->fetch()['total'];

            // Unique songs played
            $stmt = $db->prepare("SELECT COUNT(DISTINCT song_id) as unique_songs FROM play_history WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $uniqueSongs = $stmt->fetch()['unique_songs'];

            // Top played songs
            $stmt = $db->prepare("
                SELECT s.*, a.name as artist_name, COUNT(ph.id) as play_count
                FROM play_history ph
                JOIN songs s ON ph.song_id = s.id
                LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
                LEFT JOIN artists a ON sa.artist_id = a.id
                WHERE ph.user_id = :user_id
                GROUP BY s.id
                ORDER BY play_count DESC
                LIMIT 5
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $topSongs = $stmt->fetchAll();

            // Total listening time (in seconds)
            $stmt = $db->prepare("
                SELECT SUM(s.duration) as total_time
                FROM play_history ph
                JOIN songs s ON ph.song_id = s.id
                WHERE ph.user_id = :user_id
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $totalTime = $stmt->fetch()['total_time'] ?? 0;

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_plays' => (int)$totalPlays,
                    'unique_songs' => (int)$uniqueSongs,
                    'total_listening_time' => (int)$totalTime,
                    'top_songs' => $topSongs
                ]
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
