<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAuthView();

$userId = getUserId();
$database = new Database();
$db = $database->getConnection();

$query = "SELECT username, email, full_name, bio, avatar_url, created_at,
          (SELECT COUNT(*) FROM playlists WHERE user_id = :user_id) as playlist_count,
          (SELECT COUNT(*) FROM user_favorites WHERE user_id = :user_id_2) as favorites_count
          FROM users WHERE id = :user_id_3";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':user_id_2', $userId);
$stmt->bindParam(':user_id_3', $userId);
$stmt->execute();
$user = $stmt->fetch();

// Fetch User's Playlists
$queryPlaylists = "SELECT * FROM playlists WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 6";
$stmtPlaylists = $db->prepare($queryPlaylists);
$stmtPlaylists->bindParam(':user_id', $userId);
$stmtPlaylists->execute();
$userPlaylists = $stmtPlaylists->fetchAll();

// Fetch User's Favorites (Songs)
// Fetch User's Favorites (Songs)
$queryFavorites = "SELECT s.*, a.name as artist_name
                   FROM user_favorites uf 
                   JOIN songs s ON uf.favoritable_id = s.id 
                   LEFT JOIN song_artists sa ON s.id = sa.song_id AND sa.is_primary = 1
                   LEFT JOIN artists a ON sa.artist_id = a.id
                   WHERE uf.user_id = :user_id 
                   AND uf.favoritable_type = 'song'
                   ORDER BY uf.created_at DESC LIMIT 6";
$stmtFavorites = $db->prepare($queryFavorites);
$stmtFavorites->bindParam(':user_id', $userId);
$stmtFavorites->execute();
$userFavorites = $stmtFavorites->fetchAll();

$updateSuccess = false;
$updateErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $bio = $_POST['bio'] ?? '';

    // Convert array inputs to string if needed (shouldn't happen with standard form, but safe fallback)
    if (is_array($fullName))
        $fullName = '';
    if (is_array($bio))
        $bio = '';

    try {
        $db->beginTransaction();

        // 1. Update text fields
        $query = "UPDATE users SET full_name = :full_name, bio = :bio WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':bio', $bio);
        $stmt->bindParam(':user_id', $userId);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update profile info.");
        }

        // 2. Handle Avatar Upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.");
            }

            if ($file['size'] > $maxSize) {
                throw new Exception("File size too large. Max 5MB.");
            }

            // Create upload dir if not exists
            $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique name
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            $publicPath = '../assets/uploads/avatars/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update DB with new avatar URL
                // Note: We use the relative path for the view to access it
                $query = "UPDATE users SET avatar_url = :avatar_url WHERE id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':avatar_url', $publicPath);
                $stmt->bindParam(':user_id', $userId);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to update avatar in database.");
                }

                // Update local user array so view reflects changes immediately
                $user['avatar_url'] = $publicPath;
            } else {
                throw new Exception("Failed to move uploaded file.");
            }
        }

        $db->commit();
        $updateSuccess = true;

        // Update local vars
        $user['full_name'] = $fullName;
        $user['bio'] = $bio;

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Profile update error: " . $e->getMessage());
        $updateErrors['general'] = $e->getMessage();
    }
}
