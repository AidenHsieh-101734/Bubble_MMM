<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/validation.php';
require_once __DIR__ . '/utils/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username)) {
        $errors['username'] = 'Gebruikersnaam is verplicht';
    } elseif (!validateUsername($username)) {
        $errors['username'] = 'Gebruikersnaam moet minstens 3 tekens zijn';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is verplicht';
    } elseif (!validateEmail($email)) {
        $errors['email'] = 'Ongeldig email adres';
    }

    if (empty($password)) {
        $errors['password'] = 'Wachtwoord is verplicht';
    } elseif (!validatePassword($password)) {
        $errors['password'] = 'Wachtwoord moet minstens 8 tekens zijn';
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Wachtwoorden komen niet overeen';
    }

    if (empty($errors)) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT id FROM users WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $errors['username'] = 'Gebruikersnaam is al in gebruik';
            }

            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'Email is al in gebruik';
            }

            if (empty($errors)) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $query = "INSERT INTO users (username, email, password_hash) 
                          VALUES (:username, :email, :password_hash)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password_hash', $passwordHash);

                if ($stmt->execute()) {
                    $userId = $db->lastInsertId();
                    setUserSession($userId, $username, $email);
                    header('Location: ../view/profile_view.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors['general'] = 'Er is een fout opgetreden. Probeer het opnieuw.';
        }
    }
}
