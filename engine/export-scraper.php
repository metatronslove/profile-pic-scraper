<?php
// engine/export-scraper.php
require_once '../config.php';

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="scraper-settings-' . date('Y-m-d') . '.json"');

$platformsPath = '../scraper/platforms.json';
if (file_exists($platformsPath)) {
    echo file_get_contents($platformsPath);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ayarlar bulunamadı']);
}
?>
