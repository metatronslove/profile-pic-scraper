<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sosyal Medya Profil Görüntüleyici' ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Ana stiller -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/notifications.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/media-gallery.css">
    
    <!-- Özel CSS (dashboard'dan düzenlenebilir) -->
    <?php if (file_exists(__DIR__ . '/../../assets/css/custom.css')): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css?t=<?= filemtime(__DIR__ . '/../../assets/css/custom.css') ?>">
    <?php endif; ?>
    
    <script>
        window.baseUrl = '<?= BASE_URL ?>';
    </script>
</head>
<body>
    <!-- Loading Overlay (CSS + JS ile yönetiliyor, ayrı dosya gerekmiyor) -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; color: white; font-size: 1.5rem;">
        <i class="fas fa-spinner fa-spin" style="font-size: 3rem; margin-bottom: 20px;"></i>
        <div>İşlem yapılıyor, lütfen bekleyin...</div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer" class="notification-container"></div>

    <!-- Sayfa içeriği burada devam eder -->
