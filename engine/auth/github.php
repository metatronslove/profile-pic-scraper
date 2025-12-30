<?php
require_once '../../config.php';

$params = [
    'client_id' => GITHUB_CLIENT_ID,
    'redirect_uri' => GITHUB_REDIRECT_URI,
    'scope' => 'read:user'
];

header('Location: https://github.com/login/oauth/authorize?' . http_build_query($params));
exit;
?>
