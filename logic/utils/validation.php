<?php

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUsername($username)
{
    return preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $username);
}

function validatePassword($password)
{
    return strlen($password) >= 6;
}

function sanitizeString($string)
{
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

function validateRequired($data, $requiredFields)
{
    $errors = [];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[$field] = ucfirst($field) . ' is required';
        }
    }

    return $errors;
}

function validateInteger($value, $min = null, $max = null)
{
    if (!filter_var($value, FILTER_VALIDATE_INT)) {
        return false;
    }

    $intValue = (int) $value;

    if ($min !== null && $intValue < $min) {
        return false;
    }

    if ($max !== null && $intValue > $max) {
        return false;
    }

    return true;
}

function createSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function validateFileExtension($filename, $allowedExtensions)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExtensions);
}

function getJsonInput()
{
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

function getRequestData()
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        return getJsonInput();
    }

    return $_POST;
}
