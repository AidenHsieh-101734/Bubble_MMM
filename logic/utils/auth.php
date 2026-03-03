<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/response.php';

function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser()
{
    if (!isAuthenticated()) {
        return null;
    }

    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, username, email, full_name, avatar_url, bio, created_at 
                  FROM users WHERE id = :user_id AND is_active = 1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get current user error: " . $e->getMessage());
        return null;
    }
}

function requireAuth()
{
    if (!isAuthenticated()) {
        sendUnauthorized('Please login to continue');
    }
}

function isAdmin($userId = null)
{
    $userId = $userId ?? getCurrentUserId();

    if (!$userId) {
        return false;
    }

    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT COUNT(*) as count FROM admin_users WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        error_log("Check admin error: " . $e->getMessage());
        return false;
    }
}

function requireAdmin()
{
    requireAuth();

    if (!isAdmin()) {
        sendForbidden('Admin access required');
    }
}

function setUserSession($userId, $username, $email)
{
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['last_activity'] = time();
}

function clearUserSession()
{
    session_unset();
    session_destroy();
}

function updateLastLogin($userId)
{
    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "UPDATE users SET last_login = NOW() WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Update last login error: " . $e->getMessage());
    }
}

function getUserId()
{
    return getCurrentUserId();
}

function requireAuthView()
{
    if (!isAuthenticated()) {
        header('Location: login_view.php');
        exit;
    }
}

function redirectIfAuthenticated($location = 'profile_view.php')
{
    if (isAuthenticated()) {
        header('Location: ' . $location);
        exit;
    }
}
