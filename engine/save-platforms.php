<?php
// engine/save-platforms.php
require_once '../config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Geçersiz JSON']);
    exit;
}

file_put_contents('../scraper/platforms.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Platform ayarları kaydedildi']);
?>
