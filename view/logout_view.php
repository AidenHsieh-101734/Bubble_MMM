<?php
/**
 * Logout
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear session
require_once __DIR__ . '/../logic/utils/auth.php';
clearUserSession();

// Redirect to login
header('Location: login_view.php');
exit;
