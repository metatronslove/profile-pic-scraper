<?php
// engine/auth/callback.php
require_once '../../config.php';

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';

if (!$code || !in_array($provider, ['google', 'github', 'facebook'])) {
    die('Geçersiz istek');
}

$userData = null;

switch ($provider) {
    case 'google':
        $tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query([
                    'code'          => $code,
                    'client_id'     => GOOGLE_CLIENT_ID,
                    'client_secret' => GOOGLE_CLIENT_SECRET,
                    'redirect_uri'  => GOOGLE_REDIRECT_URI,
                    'grant_type'    => 'authorization_code'
                ])
            ]
        ]));
        $token = json_decode($tokenResponse, true);
        if (isset($token['access_token'])) {
            $userResponse = file_get_contents("https://www.googleapis.com/oauth2/v3/userinfo?access_token={$token['access_token']}");
            $userData = json_decode($userResponse, true);
        }
        break;

    case 'github':
        $tokenResponse = file_get_contents('https://github.com/login/oauth/access_token', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
                'content' => http_build_query([
                    'client_id'     => GITHUB_CLIENT_ID,
                    'client_secret' => GITHUB_CLIENT_SECRET,
                    'code'          => $code,
                    'redirect_uri'  => GITHUB_REDIRECT_URI
                ])
            ]
        ]));
        $token = json_decode($tokenResponse, true);
        if (isset($token['access_token'])) {
            $userResponse = file_get_contents('https://api.github.com/user', false, stream_context_create([
                'http' => ['header' => "Authorization: Bearer {$token['access_token']}\r\nUser-Agent: ProfileScraper"]
            ]));
            $userData = json_decode($userResponse, true);
        }
        break;

    case 'facebook':
        $tokenResponse = file_get_contents("https://graph.facebook.com/v20.0/oauth/access_token?client_id=" . FACEBOOK_APP_ID . "&redirect_uri=" . urlencode(FACEBOOK_REDIRECT_URI) . "&client_secret=" . FACEBOOK_APP_SECRET . "&code=" . urlencode($code));
        $token = json_decode($tokenResponse, true);
        if (isset($token['access_token'])) {
            $userResponse = file_get_contents("https://graph.facebook.com/me?fields=id,name,email,picture&access_token=" . $token['access_token']);
            $userData = json_decode($userResponse, true);
            if (isset($userData['picture']['data']['url'])) {
                $userData['picture'] = $userData['picture']['data']['url'];
            }
        }
        break;
}

if ($userData && isset($userData['id'])) {
    $_SESSION['user'] = [
        'id'       => $userData['id'],
        'name'     => $userData['name'] ?? $userData['login'],
        'email'    => $userData['email'] ?? null,
        'picture'  => $userData['picture'] ?? null,
        'provider' => $provider
    ];
}

$providerId = $provider . '_' . $userData['id'];
if (strpos(ADMIN_USERS, $providerId) !== false) {
    $_SESSION['admin'] = true;
} else {
    $_SESSION['admin'] = false; // Sadece görüntüleme izni
}

header('Location: ' . BASE_URL . 'dashboard.php');
exit;
?>
