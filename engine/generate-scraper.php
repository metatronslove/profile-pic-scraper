<?php
// engine/generate-scraper.php
require_once '../config.php';

// Sadece dashboard'dan gelen yetkili istekler için (geçici güvenlik)
if (!isset($_POST['generate']) || !isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die(json_encode(['success' => false, 'error' => 'Yetkisiz erişim']));
}

$platformsPath = '../scraper/platforms.json';
if (!file_exists($platformsPath)) {
    die(json_encode(['success' => false, 'error' => 'platforms.json bulunamadı']));
}

$platforms = json_decode(file_get_contents($platformsPath), true);
if (!is_array($platforms)) {
    die(json_encode(['success' => false, 'error' => 'platforms.json geçersiz']));
}

// --------------------------
// scraper.php OLUŞTURULUYOR
// --------------------------
$phpCode = "<?php\n";
$phpCode .= "// scraper.php - OTOMATİK OLUSTURULDU - DÜZENLEMEYİN!\n";
$phpCode .= "// " . date('Y-m-d H:i:s') . " tarihinde generate-scraper.php tarafından oluşturuldu\n\n";
$phpCode .= "require_once '" . __DIR__ . "/../config.php';\n\n";

// extractIdentifierFromUrl fonksiyonu
$phpCode .= "function extractIdentifierFromUrl(\$url, \$platform = null) {\n";
$phpCode .= "    \$platforms = json_decode(file_get_contents(__DIR__ . '/platforms.json'), true);\n";
$phpCode .= "    if (\$platform && isset(\$platforms[\$platform])) {\n";
$phpCode .= "        \$plat = \$platforms[\$platform];\n";
$phpCode .= "        foreach (\$plat['detect_patterns'] as \$pattern) {\n";
$phpCode .= "            if (stripos(\$url, \$pattern) !== false) {\n";
$phpCode .= "                if (preg_match('/' . str_replace('{identifier}', '([\\w.\\-]+)', preg_quote(\$plat['profile_url_pattern'], '/')) . '/i', \$url, \$m)) {\n";
$phpCode .= "                    return \$m[1];\n";
$phpCode .= "                }\n";
$phpCode .= "            }\n";
$phpCode .= "        }\n";
$phpCode .= "    }\n";
$phpCode .= "    foreach (\$platforms as \$key => \$plat) {\n";
$phpCode .= "        if (!\$plat['enabled']) continue;\n";
$phpCode .= "        foreach (\$plat['detect_patterns'] as \$pattern) {\n";
$phpCode .= "            if (stripos(\$url, \$pattern) !== false) {\n";
$phpCode .= "                if (preg_match('/' . str_replace('{identifier}', '([\\w.\\-]+)', preg_quote(\$plat['profile_url_pattern'], '/')) . '/i', \$url, \$m)) {\n";
$phpCode .= "                    return ['identifier' => \$m[1], 'platform' => \$key];\n";
$phpCode .= "                }\n";
$phpCode .= "            }\n";
$phpCode .= "        }\n";
$phpCode .= "    }\n";
$phpCode .= "    return null;\n";
$phpCode .= "}\n\n";

// Ana getProfileData fonksiyonu
$phpCode .= "function getProfileData(\$url) {\n";
$phpCode .= "    global \$pdo;\n";
$phpCode .= "    \$info = extractIdentifierFromUrl(\$url);\n";
$phpCode .= "    if (!\$info || is_string(\$info)) return ['success' => false, 'error' => 'Geçersiz URL'];\n";
$phpCode .= "    \$platform = \$info['platform'];\n";
$phpCode .= "    \$identifier = \$info['identifier'];\n\n";
$phpCode .= "    \$platforms = json_decode(file_get_contents(__DIR__ . '/platforms.json'), true);\n";
$phpCode .= "    \$plat = \$platforms[\$platform] ?? null;\n";
$phpCode .= "    if (!\$plat || !\$plat['enabled']) return ['success' => false, 'error' => 'Platform desteklenmiyor'];\n\n";

$phpCode .= "    \$result = ['platform' => \$plat['name'], 'identifier' => \$identifier, 'original_url' => \$url, 'contents' => []];\n\n";

// Yöntemleri priority sırasına göre sırala ve dene
$phpCode .= "    \$methods = \$plat['methods'] ?? [];\n";
$phpCode .= "    usort(\$methods, fn(\$a,\$b) => (\$a['priority'] ?? 99) <=> (\$b['priority'] ?? 99));\n\n";
$phpCode .= "    foreach (\$methods as \$method) {\n";
$phpCode .= "        if (!(\$method['enabled'] ?? false)) continue;\n\n";

$phpCode .= "        if (\$method['type'] === 'server_php') {\n";
$phpCode .= "            // cURL ile API veya HTML çekme\n";
$phpCode .= "            \$endpoint = str_replace('{username}', \$identifier, \$method['endpoint'] ?? '');\n";
$phpCode .= "            if (!empty(\$endpoint)) {\n";
$phpCode .= "                \$ch = curl_init(\$endpoint);\n";
$phpCode .= "                curl_setopt_array(\$ch, [\n";
$phpCode .= "                    CURLOPT_RETURNTRANSFER => true,\n";
$phpCode .= "                    CURLOPT_FOLLOWLOCATION => true,\n";
$phpCode .= "                    CURLOPT_SSL_VERIFYPEER => false,\n";
$phpCode .= "                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'\n";
$phpCode .= "                ]);\n";
$phpCode .= "                \$headers = [];\n";
foreach (['headers' => ''] as $k => $v) { // placeholder
    $phpCode .= "                foreach (\$method['$k'] ?? [] as \$h => \$val) {\n";
    $phpCode .= "                    \$headers[] = \"\$h: \" . str_replace('{username}', \$identifier, \$val);\n";
    $phpCode .= "                }\n";
}
$phpCode .= "                if (!empty(\$headers)) curl_setopt(\$ch, CURLOPT_HTTPHEADER, \$headers);\n";
$phpCode .= "                \$response = curl_exec(\$ch);\n";
$phpCode .= "                \$httpCode = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);\n";
$phpCode .= "                curl_close(\$ch);\n\n";
$phpCode .= "                if (\$httpCode === 200 && \$response) {\n";
$phpCode .= "                    // JSON mu HTML mi kontrol et\n";
$phpCode .= "                    \$json = json_decode(\$response, true);\n";
$phpCode .= "                    if (\$json) {\n";
$phpCode .= "                        // JSON path'lerle içerik çıkar\n";
$phpCode .= "                        foreach (\$plat['extractable_content'] ?? [] as \$content => \$enabled) {\n";
$phpCode .= "                            if (!\$enabled) continue;\n";
$phpCode .= "                            \$path = \$method['json_path_' . \$content] ?? null;\n";
$phpCode .= "                            if (\$path) {\n";
$phpCode .= "                                \$value = eval('return ' . \$path . ' ?? null;');\n"; // basit eval, dikkat!
$phpCode .= "                                if (\$value) {\n";
$phpCode .= "                                    if (str_contains(\$content, 'pic') || str_contains(\$content, 'image')) {\n";
$phpCode .= "                                        foreach (\$method['optimize_rules'] ?? [] as \$rule) {\n";
$phpCode .= "                                            \$value = str_replace(\$rule['find'], \$rule['replace'], \$value);\n";
$phpCode .= "                                        }\n";
$phpCode .= "                                    }\n";
$phpCode .= "                                    \$result['contents'][\$content] = \$value;\n";
$phpCode .= "                                }\n";
$phpCode .= "                            }\n";
$phpCode .= "                        }\n";
$phpCode .= "                    }\n";
$phpCode .= "                }\n";
$phpCode .= "            }\n";
$phpCode .= "        }\n\n";

$phpCode .= "        // Buraya browser_js, composer_api gibi diğer method tipleri eklenecek (ileride)\n";
$phpCode .= "        if (!empty(\$result['contents'])) {\n";
$phpCode .= "            return ['success' => true] + \$result;\n";
$phpCode .= "        }\n";
$phpCode .= "    }\n\n";

$phpCode .= "    return ['success' => false, 'error' => 'Hiçbir yöntem çalışmadı'];\n";
$phpCode .= "}\n";

file_put_contents('../scraper/scraper.php', $phpCode);

// scraper.js - Zenginleştirilmiş
$jsCode = "// scraper/scraper.js - OTOMATİK OLUSTURULDU - " . date('Y-m-d H:i:s') . "\n\n";
$jsCode .= "window.ScraperBridge = {\n";
$jsCode .= "    config: " . json_encode($platforms, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ",\n";
$jsCode .= file_get_contents(__DIR__ . '/scraper_bridge_template.js'); // Aşağıdaki kodu ayrı dosya olarak tutabilirsin
$jsCode .= "\n};\n\n";
$jsCode .= "document.addEventListener('DOMContentLoaded', () => window.ScraperBridge.init());\n";

file_put_contents('../scraper/scraper.js', $jsCode);

echo json_encode([
    'success' => true,
    'message' => 'Scraper dosyaları başarıyla güncellendi!',
    'generated_at' => date('Y-m-d H:i:s')
]);
?>
