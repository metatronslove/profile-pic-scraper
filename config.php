<?php
// config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Istanbul');

define('DB_HOST', 'your-database-url');
define('DB_NAME', 'your-database-name');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');

define('BASE_DIR', __DIR__);
define('CACHE_DIR', __DIR__ . '/cache/');
define('LOG_DIR', __DIR__ . '/logs/');
define('SCRAPER_DIR', __DIR__ . '/scraper/');
define('ENGINE_DIR', __DIR__ . '/engine/');

// OAuth App Key'leri - Kullanıcı dashboard'dan kendi key'lerini girecek (ileride ayarlar sayfası)
define('GOOGLE_CLIENT_ID', 'your-google-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your-google-secret');
define('GOOGLE_REDIRECT_URI', BASE_URL . 'engine/auth/callback.php?provider=google');

define('GITHUB_CLIENT_ID', 'your-github-client-id');
define('GITHUB_CLIENT_SECRET', 'your-github-secret');
define('GITHUB_REDIRECT_URI', BASE_URL . 'engine/auth/callback.php?provider=github');

define('FACEBOOK_APP_ID', 'your-facebook-app-id');
define('FACEBOOK_APP_SECRET', 'your-facebook-secret');
define('FACEBOOK_REDIRECT_URI', BASE_URL . 'engine/auth/callback.php?provider=facebook');

if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);
if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0755, true);

session_start();

// Veritabanı
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

function baseUrl($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $protocol . '://' . $host . $base . '/' . ltrim($path, '/');
}
define('BASE_URL', baseUrl());
?>
