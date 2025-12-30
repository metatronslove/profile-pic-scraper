<?php
// scraper/scraper.php - OTOMATİK OLUSTURULDU + ORİJİNAL ÖZELLİKLER ENTEGRE

require_once __DIR__ . '/../config.php';

// Dinamik advancedCurlRequest — platforma özel ayarlar platforms.json'dan gelir
function advancedCurlRequest($url, $options = [], $platform = null) {
    $ch = curl_init();

    // Platform ayarlarını yükle
    $platforms = json_decode(file_get_contents(__DIR__ . '/platforms.json'), true);
    $plat = $platforms[$platform] ?? null;

    $defaultOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9,tr;q=0.8',
            'Cache-Control: no-cache',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1'
        ]
    ];

    // Platforma özel User-Agent
    $userAgent = $plat['user_agent'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    $defaultOptions[CURLOPT_USERAGENT] = $userAgent;

    // Platforma özel varsayılan header'lar
    $extraHeaders = $plat['default_headers'] ?? [];
    $finalHeaders = $defaultOptions[CURLOPT_HTTPHEADER];
    foreach ($extraHeaders as $key => $value) {
        $finalHeaders[] = "$key: $value";
    }
    $defaultOptions[CURLOPT_HTTPHEADER] = $finalHeaders;

    curl_setopt_array($ch, array_replace($defaultOptions, $options));

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Ana fonksiyon - orijinal getProfileImageWithAllMedia mantığına benzer
function getProfileData($url) {
    global $pdo;

    $info = extractIdentifierFromUrl($url);
    if (!$info || is_string($info)) return ['success' => false, 'error' => 'Geçersiz URL'];

    $platform = $info['platform'];
    $identifier = $info['identifier'];

    $platforms = json_decode(file_get_contents(__DIR__ . '/platforms.json'), true);
    $plat = $platforms[$platform] ?? null;
    if (!$plat || !$plat['enabled']) return ['success' => false, 'error' => 'Platform desteklenmiyor'];

    $result = [
        'success' => true,
        'platform' => $plat['name'],
        'identifier' => $identifier,
        'original_url' => $url,
        'all_media' => [],
        'best_profile' => []
    ];

    $methods = $plat['methods'] ?? [];
    usort($methods, fn($a,$b) => ($a['priority'] ?? 99) <=> ($b['priority'] ?? 99));

    foreach ($methods as $method) {
        if (!($method['enabled'] ?? false)) continue;

        if ($method['type'] === 'server_php') {
            $endpoint = str_replace('{username}', $identifier, $method['endpoint'] ?? '');
            if ($endpoint) {
                $res = advancedCurlRequest($endpoint, [], $platform);
                if ($res['http_code'] === 200) {
                    $json = json_decode($res['response'], true);
                    if ($json) {
                        // Profil resmi bul
                        $picPath = $method['json_path_profile_pic_hd'] ?? $method['json_path_profile_pic'] ?? null;
                        if ($picPath && ($pic = eval("return \$json$picPath ?? null;"))) {
                            foreach ($method['optimize_rules'] ?? [] as $rule) {
                                $pic = str_replace($rule['find'], $rule['replace'], $pic);
                            }
                            $result['best_profile'] = ['url' => $pic];
                        }
                        // Postlar vb. diğer içerikler
                        $postsPath = $method['json_path_posts'] ?? null;
                        if ($postsPath) {
                            $posts = eval("return \$json$postsPath ?? [];");
                            $result['all_media'] = $posts; // orijinal yapıyı koru
                        }
                    }
                }
            }
        }

        if (!empty($result['best_profile']) || !empty($result['all_media'])) {
            break; // ilk başarılı yöntem yeterli
        }
    }

    // Veritabanına kaydet (orijinal recent profiles mantığı)
    if ($result['success']) {
        $profileUrl = $url;
        $base64 = null;
        if (!empty($result['best_profile']['url'])) {
            $imgData = downloadImageToBase64($result['best_profile']['url']);
            if ($imgData) $base64 = $imgData;
        }
        saveProfileToDatabase($profileUrl, $platform, $base64, $result['best_profile']['url'] ?? null);
    }

    return $result;
}

// Yardımcı fonksiyon (orijinalden)
function downloadImageToBase64($url) {
    $res = advancedCurlRequest($url);
    if ($res['http_code'] === 200 && $res['response']) {
        return base64_encode($res['response']);
    }
    return null;
}

function saveProfileToDatabase($profileUrl, $platform, $base64Image = null, $originalUrl = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO recent_profiles (profile_url, platform, profile_image_base64, original_image_url, viewed_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$profileUrl, $platform, $base64Image, $originalUrl]);
    } catch (Exception $e) {
        error_log("DB save error: " . $e->getMessage());
    }
}
?>
