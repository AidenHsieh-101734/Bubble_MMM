<?php
require_once __DIR__ . '/config/database.php';

function getAdminStats()
{
    try {
        $db = (new Database())->getConnection();

        // Count total users
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $users = $stmt->fetch()['count'];

        // Count active sessions (users logged in within last 24h as an approximation, or just active flag)
        // Using last_login within 24h for active sessions/daily active users
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $activeSessions = $stmt->fetch()['count'];

        // Count total songs
        $stmt = $db->query("SELECT COUNT(*) as count FROM songs");
        $songs = $stmt->fetch()['count'];

        // Count articles
        $stmt = $db->query("SELECT COUNT(*) as count FROM articles");
        $articles = $stmt->fetch()['count'];

        return [
            'users' => $users,
            'active_sessions' => $activeSessions,
            'songs' => $songs,
            'articles' => $articles
        ];
    } catch (Exception $e) {
        error_log("Error fetching admin stats: " . $e->getMessage());
        return [
            'users' => 0,
            'active_sessions' => 0,
            'songs' => 0,
            'articles' => 0
        ];
    }
}

function getRecentUsers($limit = 5)
{
    try {
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT username, email, created_at, is_active FROM users ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching recent users: " . $e->getMessage());
        return [];
    }
}

function getContentOverview($limit = 5)
{
    try {
        $db = (new Database())->getConnection();

        // Fetch recent songs
        $stmt = $db->prepare("
            SELECT 'Song' as type, title, created_at as date, 
                   (SELECT name FROM artists WHERE id = songs.album_id LIMIT 1) as author 
            FROM songs 
            ORDER BY created_at DESC LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $songs = $stmt->fetchAll();

        // Fetch recent articles
        $stmt = $db->prepare("
            SELECT 'Article' as type, title, created_at as date,
                   (SELECT username FROM users WHERE id = articles.author_id) as author
            FROM articles
            ORDER BY created_at DESC LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll();

        // Merge and sort
        $content = array_merge($songs, $articles);
        usort($content, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($content, 0, $limit);
    } catch (Exception $e) {
        error_log("Error fetching content overview: " . $e->getMessage());
        return [];
    }
}
