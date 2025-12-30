<?php
require_once '../../config.php';

if (!defined('GOOGLE_CLIENT_ID') || GOOGLE_CLIENT_ID === 'your-google-client-id') {
    die('Google OAuth ayarları yapılmamış.');
}

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
    'access_type' => 'offline',
    'prompt' => 'consent'
];

header('Location: https://accounts.google.com/o/oauth2/auth?' . http_build_query($params));
exit;
?>
