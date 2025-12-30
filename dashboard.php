<?php
require_once 'config.php';

if (empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL);
    exit;
}

$pageTitle = "Ayarlar Dashboard";
$extraScripts = ['dashboard.js', 'auth.js'];

require 'assets/templates/header.php';
require 'assets/templates/modals.php'; // Login modalı da burada
?>

<div class="container">
    <!-- Header -->
    <div class="header" style="margin: -20px -20px 40px -20px; border-radius: 16px 16px 0 0;">
        <h1>Ayarlar Dashboard</h1>
        <p class="tagline">Platformları yönetin · Yeni yöntemler ekleyin · Görünümü özelleştirin</p>
    </div>

    <!-- Kullanıcı Bilgisi -->
    <div class="user-info" style="margin-bottom: 30px; display: flex; align-items: center; gap: 15px; font-size: 1.1rem;">
        <img src="<?= htmlspecialchars($_SESSION['user']['picture'] ?? 'https://via.placeholder.com/50') ?>" width="50" height="50" style="border-radius: 50%;">
        <div>
            <strong><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Yönetici') ?></strong><br>
            <a href="<?= BASE_URL ?>engine/auth/logout.php" style="color: var(--error); font-size: 0.9rem;">Çıkış Yap</a>
            <span style="margin: 0 10px;">·</span>
            <a href="<?= BASE_URL ?>" style="color: var(--primary);">Ana Sayfaya Dön</a>
        </div>
    </div>

    <!-- Hızlı Eylemler -->
    <div class="action-buttons" style="margin-bottom: 30px; display: flex; flex-wrap: wrap; gap: 15px;">
        <button onclick="openEditModal()" class="btn-action">
            <i class="fas fa-plus"></i> Yeni Platform Ekle
        </button>
        <button onclick="generateScraper()" class="btn-action">
            <i class="fas fa-sync-alt"></i> Scraper'ları Yeniden Oluştur
        </button>
        <a href="<?= BASE_URL ?>engine/export-scraper.php" class="btn-action secondary">
            <i class="fas fa-download"></i> Tüm Ayarları Dışa Aktar
        </a>
    </div>

    <!-- Platform Tablosu -->
    <div class="card">
        <h2 style="margin-bottom: 20px;">
            <i class="fab fa-hubspot"></i> Platformlar
        </h2>
        <div class="table-responsive">
            <table id="platformTable" class="full-width">
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Durum</th>
                        <th>Son Profiller Limiti</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- dashboard.js ile dinamik doldurulacak -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Composer Rehberi -->
    <div class="card success" style="margin-top: 30px;">
        <h2><i class="fas fa-gem"></i> Composer Paketleri InfinityFree'de Çalışır!</h2>
        <p>Resmi API'lerle (Instagram Graph, Twitter v2 vb.) daha stabil erişim için:</p>
        <ol>
            <li>Yerelinizde <code>composer require league/oauth2-facebook guzzlehttp/guzzle</code> vb. çalıştırın</li>
            <li>Oluşan <code>vendor/</code> klasörünü FTP ile yükleyin</li>
            <li>İlgili dosyalara <code>require 'vendor/autoload.php';</code> ekleyin</li>
        </ol>
    </div>

    <!-- Tema Düzenleyici -->
    <div class="card" style="margin-top: 30px;">
        <h2><i class="fas fa-palette"></i> Tema & Stil Düzenleyici</h2>
        <form action="<?= BASE_URL ?>engine/save-custom-css.php" method="POST">
            <textarea name="custom_css" id="customCssEditor" rows="12" style="width:100%; font-family: 'Courier New', monospace; padding: 15px; border-radius: 8px; border: 1px solid var(--light-dark); background: #f8f9fa;">
<?= file_exists('assets/css/custom.css') ? htmlspecialchars(file_get_contents('assets/css/custom.css')) : "/* Özel CSS kodlarınızı buraya yazın */\n\n/* Örnek: Karanlık mod */\n/*\nbody { background: #121212; color: #e0e0e0; }\n.card { background: #1e1e1e; }\n*/" ?>
            </textarea>
            <button type="submit" class="btn-action" style="margin-top: 15px;">
                <i class="fas fa-save"></i> Kaydet ve Uygula
            </button>
        </form>
    </div>

    <!-- Yapay Zeka Rehberi -->
    <div class="card info" style="margin-top: 30px;">
        <h2><i class="fas fa-robot"></i> Yapay Zeka ile Yöntem Geliştirme</h2>
        <p>Platform değiştiğinde şu promptu Grok/ChatGPT'ye verin:</p>
        <pre style="background: #f0f0f0; padding: 15px; border-radius: 8px; overflow-x: auto;">
Instagram profil resmi şu endpointten geliyor: https://i.instagram.com/api/v1/users/web_profile_info/?username=xyz
Header: x-ig-app-id: 936619743392459
JSON path: data.user.profile_pic_url_hd
s150x150 → s1080x1080 optimize et
Bunu PHP cURL ile nasıl çekerim?</pre>
    </div>
</div>

<!-- Platform Düzenleme Modal'ı - Orijinal stillerle %100 uyumlu -->
<div id="editModal" class="modal">
    <div class="modal-content large">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-header" style="background: var(--primary);">
            <h2 id="modalTitle" style="margin:0; color:white;">Platform Düzenle</h2>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <input type="hidden" id="platformKey">
                
                <div class="input-group" style="margin-bottom: 20px;">
                    <label>Platform Adı</label>
                    <input type="text" id="name" required>
                </div>
                
                <div class="input-group HIZLI">
                    <label>Renk (HEX)</label>
                    <input type="color" id="color" value="#E1306C">
                </div>
                
                <div class="input-group">
                    <label>İkon (FontAwesome)</label>
                    <input type="text" id="icon" placeholder="fab fa-instagram">
                </div>
                
                <div class="input-group">
                    <label>URL Tespit Pattern (virgülle ayrılmış)</label>
                    <input type="text" id="detect_patterns" placeholder="instagram.com, i.instagram.com">
                </div>
                
                <div class="input-group">
                    <label>Profil URL Şablonu</label>
                    <input type="text" id="profile_url_pattern" placeholder="https://www.instagram.com/{identifier}/">
                </div>

                <h3 style="margin: 30px 0 15px;">Çekilecek İçerikler</h3>
                <div class="checkbox-group" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <label><input type="checkbox" id="content_profile_picture" checked> Profil Resmi</label>
                    <label><input type="checkbox" id="content_cover_photo"> Kapak Resmi</label>
                    <label><input type="checkbox" id="content_highlighted_stories"> Öne Çıkanlar</label>
                    <label><input type="checkbox" id="content_recent_posts"> Son Postlar</label>
                    <label><input type="checkbox" id="content_post_thumbnails"> Post Thumbnail</label>
                </div>

                <h3 style="margin: 30px 0 15px;">Scraping Yöntemleri</h3>
                <div id="methodsContainer"></div>
                <button type="button" onclick="addNewMethod()" class="btn-action secondary" style="margin-top: 10px;">
                    <i class="fas fa-plus"></i> Yeni Yöntem Ekle
                </button>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" onclick="savePlatform()" class="btn-action">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'assets/templates/footer.php'; ?>
