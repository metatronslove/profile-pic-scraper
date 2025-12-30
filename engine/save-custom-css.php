<?php
require_once '../config.php';

if (empty($_SESSION['user'])) {
    die('Yetkisiz erişim');
}

$css = $_POST['custom_css'] ?? '';

file_put_contents('../assets/css/custom.css', $css);

header('Location: ' . BASE_URL . 'dashboard.php?saved=css');
exit;
?>
