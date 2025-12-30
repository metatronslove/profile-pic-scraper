<?php
require_once 'config.php';
require_once 'scraper/scraper.php';

$pageTitle = "Sosyal Medya Profil Görüntüleyici";
$extraScripts = ['main.js', 'notifications.js', 'gallery.js', 'auth.js'];

require 'assets/templates/header.php';
require 'assets/templates/modals.php';
?>

<!-- index.php - orijinal tasarım + yeni scraper entegrasyonu -->
<div class="header">
    <h1>Sosyal Medya Profil Görüntüleyici</h1>
    <p class="tagline">Instagram, YouTube, Facebook, TikTok ve daha fazlasından profil resmi ve içerikleri görüntüleyin</p>
    
    <div class="stats-container">
        <!-- İstatistikler buraya veritabanından çekilecek -->
    </div>
</div>

<div class="main-content">
    <div class="search-section">
        <div class="search-form">
            <form id="searchForm" method="POST">
                <div class="input-group">
                    <input type="text" name="profile_url" id="profileUrl" placeholder="Profil URL'sini yapıştırın..." required>
                    <button type="submit" id="searchButton">
                        <i class="fas fa-search"></i> Görüntüle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($profileData['success'])): ?>
        <div class="result-container">
            <div class="profile-header platform-<?= strtolower($profileData['platform']) ?>">
                <img src="<?= $profileData['best_profile']['url'] ?? '' ?>" class="profile-image-large" onerror="handleImageError(this)">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($profileData['platform']) ?> Profili</h2>
                </div>
            </div>

            <?php if (!empty($profileData['all_media'])): ?>
                <div class="media-gallery-container">
                    <div class="gallery-header">
                        <h4><i class="fas fa-images"></i> Medya Galerisi</h4>
                        <div class="gallery-stats">
                            <span class="gallery-count">
                                <i class="fas fa-photo-video"></i> <?= count($profileData['all_media']) ?> öğe
                            </span>
                        </div>
                    </div>

                    <div class="media-grid">
                        <?php foreach ($profileData['all_media'] as $media): 
                            $img = $media['node']['display_url'] ?? $media['src'] ?? '';
                            if ($img):
                        ?>
                            <div class="media-item">
                                <div class="media-thumbnail">
                                    <img src="<?= $img ?>" alt="Medya">
                                    <div class="media-overlay">
                                        <button class="btn-media-view" onclick="viewMedia('<?= $img ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-media-download" onclick="downloadMedia('<?= $img ?>')">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>

                    <div class="gallery-actions">
                        <button onclick="downloadAllMedia()" class="btn-action">
                            <i class="fas fa-download"></i> Tümünü İndir
                        </button>
                        <button onclick="copyGalleryLinks()" class="btn-action secondary">
                            <i class="fas fa-copy"></i> Linkleri Kopyala
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require 'assets/templates/footer.php'; ?>
