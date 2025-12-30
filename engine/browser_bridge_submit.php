<?php
require_once '../config.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($input && $input['success']) {
    // Veritabanına kaydet, session'a at vb.
    // Şimdilik basit: index'e yönlendir
    $_SESSION['browser_bridge_data'] = $input;
}

echo json_encode(['received' => true]);
?>
