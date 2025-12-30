<?php
// proxy.php - GÜNCELLENMİŞ TAM KOD
error_reporting(0);
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Ana işlem fonksiyonu
function handleProxyRequest() {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_command':
            return handleGetCommand();
        case 'submit_html':
            return handleSubmitHtml();
        case 'direct_fetch':
            return handleDirectFetch();
        case 'test_connection':
            return handleTestConnection();
        default:
            return jsonResponse(['error' => 'Invalid action'], 400);
    }
}

// 1. Tarayıcıya komut ver: "Git şu URL'ye"
function handleGetCommand() {
    $targetUrl = $_POST['target_url'] ?? '';
    $platform = $_POST['platform'] ?? '';
    
    if (!$targetUrl || !$platform) {
        return jsonResponse(['error' => 'Missing parameters'], 400);
    }
    
    // Request ID oluştur
    $requestId = 'req_' . uniqid() . '_' . md5($targetUrl);
    
    // Session'a kaydet (bekleyen istek)
    $_SESSION['pending_requests'][$requestId] = [
        'target_url' => $targetUrl,
        'platform' => $platform,
        'created_at' => time(),
        'status' => 'pending'
    ];
    
    // Tarayıcıya komut ver
    return jsonResponse([
        'success' => true,
        'command' => 'fetch_html',
        'request_id' => $requestId,
        'target_url' => $targetUrl,
        'platform' => $platform,
        'instructions' => [
            'method' => 'iframe',
            'timeout' => 10000,
            'retry_count' => 2,
            'fallback_to_proxy' => true
        ]
    ]);
}

// 2. Tarayıcıdan gelen HTML'i al
function handleSubmitHtml() {
    $requestId = $_POST['request_id'] ?? '';
    $html = $_POST['html'] ?? '';
    $platform = $_POST['platform'] ?? '';
    
    if (!$requestId || !$html) {
        return jsonResponse(['error' => 'Missing data'], 400);
    }
    
    // HTML'i temizle
    $cleanHtml = cleanHtml($html, $platform);
    
    // Session'a kaydet
    $_SESSION['html_results'][$requestId] = [
        'html' => $cleanHtml,
        'platform' => $platform,
        'received_at' => time(),
        'length' => strlen($cleanHtml)
    ];
    
    // Pending request'i tamamlandı olarak işaretle
    if (isset($_SESSION['pending_requests'][$requestId])) {
        $_SESSION['pending_requests'][$requestId]['status'] = 'completed';
        $_SESSION['pending_requests'][$requestId]['completed_at'] = time();
    }
    
    // HTML'i parse et (isteğe bağlı)
    $parsedData = parseHtmlForPlatform($cleanHtml, $platform);
    
    return jsonResponse([
        'success' => true,
        'request_id' => $requestId,
        'html_length' => strlen($cleanHtml),
        'parsed_data' => $parsedData,
        'next_step' => 'process_data'
    ]);
}

// 3. Direkt fetch (sunucu tarafı - fallback)
function handleDirectFetch() {
    $url = $_GET['url'] ?? $_POST['url'] ?? '';
    $platform = $_GET['platform'] ?? $_POST['platform'] ?? '';
    
    if (!$url || !$platform) {
        return jsonResponse(['error' => 'Missing URL or platform'], 400);
    }
    
    // Domain kontrolü
    if (!isAllowedDomain($url)) {
        return jsonResponse(['error' => 'Domain not allowed'], 403);
    }
    
    // advancedCurlRequest fonksiyonunu kullan (functions.php'den)
    require_once __DIR__ . '/functions.php';
    
    $result = advancedCurlRequest($url, [], $platform);
    
    if ($result['http_code'] === 200) {
        $cleanHtml = cleanHtml($result['response'], $platform);
        $parsedData = parseHtmlForPlatform($cleanHtml, $platform);
        
        return jsonResponse([
            'success' => true,
            'method' => 'direct_fetch',
            'html_length' => strlen($cleanHtml),
            'parsed_data' => $parsedData
        ]);
    }
    
    return jsonResponse([
        'success' => false,
        'error' => 'Fetch failed',
        'http_code' => $result['http_code']
    ]);
}

// 4. Bağlantı testi
function handleTestConnection() {
    return jsonResponse([
        'success' => true,
        'service' => 'proxy_bridge',
        'version' => '2.0',
        'timestamp' => time(),
        'session_id' => session_id(),
        'capabilities' => [
            'command_relay',
            'html_submission',
            'direct_fetch',
            'html_parsing'
        ]
    ]);
}

// Yardımcı fonksiyonlar
function cleanHtml($html, $platform) {
    // Script'leri temizle
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
    
    // Platforma özel temizlik
    switch ($platform) {
        case 'instagram':
            // Instagram specific cleaning
            $html = preg_replace('/"config":{"viewer":null/', '', $html);
            break;
        case 'facebook':
            // Facebook specific
            $html = preg_replace('/"__spin":{"r":\d+,"t":\d+}/', '', $html);
            break;
    }
    
    return $html;
}

function parseHtmlForPlatform($html, $platform) {
    $data = [
        'profile_image' => null, 
        'posts' => [],
        'raw_html' => $html // YENİ: Ham HTML'yi de sakla
    ];
    
    // Eğer JSON response ise (Graph API)
    if (strpos($html, '{') === 0 && json_decode($html, true)) {
        $json = json_decode($html, true);
        
        // Facebook Graph API response
        if (isset($json['data']['url'])) {
            $data['profile_image'] = $json['data']['url'];
            return $data;
        }
        
        // Diğer JSON yapıları
        if (isset($json['avatar_url'])) {
            $data['profile_image'] = $json['avatar_url'];
        }
    }
    
    // og:image
    if (preg_match('/<meta[^>]*property="og:image"[^>]*content="([^"]+)"[^>]*>/i', $html, $matches)) {
        $data['profile_image'] = html_entity_decode($matches[1]);
    }
    
    return $data;
}

function isAllowedDomain($url) {
    $allowed = [
        'instagram.com',
        'youtube.com',
        'youtu.be',
        'facebook.com',
        'fb.com',
        'tiktok.com',
        'github.com'
    ];
    
    foreach ($allowed as $domain) {
        if (stripos($url, $domain) !== false) {
            return true;
        }
    }
    
    return false;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Ana işlemi başlat
handleProxyRequest();
?>
