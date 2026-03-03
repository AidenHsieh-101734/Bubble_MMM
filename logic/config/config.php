<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Amsterdam');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('BASE_PATH', dirname(dirname(__DIR__)));
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ASSETS_PATH', BASE_PATH . '/assets');

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_AUDIO_SIZE', 50 * 1024 * 1024); // 50MB
define('MAX_VIDEO_SIZE', 100 * 1024 * 1024); // 100MB

define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);

define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 days
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

$uploadDirs = [
    UPLOADS_PATH . '/images',
    UPLOADS_PATH . '/audio',
    UPLOADS_PATH . '/videos',
    UPLOADS_PATH . '/avatars',
    UPLOADS_PATH . '/covers'
];

foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
