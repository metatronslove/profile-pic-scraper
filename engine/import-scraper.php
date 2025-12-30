<?php
// engine/import-scraper.php
require_once '../config.php';

if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'error' => 'Dosya yüklenmedi']));
}

$uploaded = $_FILES['json_file']['tmp_name'];
$content = file_get_contents($uploaded);

if (json_decode($content) === null) {
    die(json_encode(['success' => false, 'error' => 'Geçersiz JSON']));
}

$target = '../scraper/platforms.json';
if (move_uploaded_file($uploaded, $target) || file_put_contents($target, $content)) {
    echo json_encode(['success' => true, 'message' => 'Ayarlar başarıyla içe aktarıldı! Lütfen "Generate Scraper" yapın.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Dosya kaydedilemedi']);
}
?>
