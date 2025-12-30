<?php
require_once '../../config.php';
header('Content-Type: application/json');

echo json_encode([
    'logged_in' => !empty($_SESSION['user']),
    'user' => $_SESSION['user'] ?? null
]);
?>
