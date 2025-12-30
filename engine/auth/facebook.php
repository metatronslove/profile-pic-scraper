<?php
// engine/auth/facebook.php
require_once '../../config.php';

$params = [
    'client_id'     => FACEBOOK_APP_ID,
    'redirect_uri'  => FACEBOOK_REDIRECT_URI,
    'state'         => bin2hex(random_bytes(16)),
    'scope'         => 'public_profile,email',
    'response_type' => 'code'
];

header('Location: https://www.facebook.com/v20.0/dialog/oauth?' . http_build_query($params));
exit;
?>
