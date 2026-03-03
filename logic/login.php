<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/validation.php';
require_once __DIR__ . '/utils/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = 'Email is verplicht';
    } elseif (!validateEmail($email)) {
        $errors['email'] = 'Ongeldig email adres';
    }

    if (empty($password)) {
        $errors['password'] = 'Wachtwoord is verplicht';
    }

    if (empty($errors)) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT id, username, email, password_hash, is_active 
                      FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $errors['general'] = 'Ongeldige email of wachtwoord';
            } else {
                $user = $stmt->fetch();

                if (!$user['is_active']) {
                    $errors['general'] = 'Account is gedeactiveerd';
                } elseif (!password_verify($password, $user['password_hash'])) {
                    $errors['general'] = 'Ongeldige email of wachtwoord';
                } else {
                    setUserSession($user['id'], $user['username'], $user['email']);
                    updateLastLogin($user['id']);
                    header('Location: ../view/profile_view.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors['general'] = 'Er is een fout opgetreden. Probeer het opnieuw.';
        }
    }
}
