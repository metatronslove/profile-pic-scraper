# Profile Pic Scraper v3.0

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://php.net)
[![InfinityFree Compatible](https://img.shields.io/badge/InfinityFree-Compatible-brightgreen)](https://infinityfree.net)

**Instagram, YouTube, Facebook, TikTok, GitHub ve daha fazlasından — private profiller dahil — yüksek çözünürlüklü profil resmi ve içerikleri çekin!**

Tamamen açık kaynak, dashboard ile yönetilebilir, klonlanabilir ve genişletilebilir bir sosyal medya scraping framework'ü.

## ✨ Özellikler

- Private profiller dahil yüksek çözünürlüklü profil resmi çekme
- Kapak resmi, öne çıkan hikayeler, son postlar ve thumbnail'lar (isteğe bağlı)
- Modern, responsive ve karanlık mod destekli tasarım
- Güçlü notification sistemi ve medya galerisi
- Dashboard ile sıfır kodlama: yeni platform ve yöntem ekleme
- JSON import/export ile yöntem paylaşımı
- Google, GitHub, Facebook ile güvenli oturum açma (OAuth2)
- Tarayıcı köprüsü (browser bridge) ile fallback
- Manuel Composer desteği (InfinityFree'de bile resmi API'ler çalışır!)
- Yapay zeka rehberi ile kolay yöntem geliştirme

*(Yakında eklenecek — sen yükleyebilirsin 😊)*

## 🚀 Kurulum (InfinityFree & Diğer Hostingler)

1. Tüm dosyaları sunucunuza yükleyin (klasör yapısını koruyun)
2. `config.php`'yi düzenleyin:
   ```php
   // Veritabanı
   define('DB_HOST', 'sqlXXX.epizy.com');
   define('DB_NAME', 'epiz_XXXXXXX');
   define('DB_USER', 'epiz_XXXXXXX');
   define('DB_PASS', 'şifreniz');

   // OAuth (isteğe bağlı ama önerilir)
   define('GOOGLE_CLIENT_ID', 'your-google-client-id.apps.googleusercontent.com');
   // ... diğer provider'lar
   ```
3. Veritabanı tablosu oluşturun (isteğe bağlı, recent profiles için):
   ```sql
-- Veritabanı: profile_pic_scraper
-- Tarih: 30 Aralık 2025

-- 1. Son Görüntülenen Profiller (Recent Profiles)
CREATE TABLE IF NOT EXISTS recent_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_url VARCHAR(1000) NOT NULL,
    platform VARCHAR(50) NOT NULL,
    identifier VARCHAR(255) NOT NULL,                    -- kullanıcı adı veya ID
    profile_image_base64 LONGTEXT NULL,                  -- base64 encoded resim (isteğe bağlı)
    original_image_url VARCHAR(1000) NULL,               -- orijinal yüksek çözünürlük URL
    cover_image_url VARCHAR(1000) NULL,                  -- kapak resmi (isteğe bağlı)
    viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_platform (platform),
    INDEX idx_viewed_at (viewed_at),
    INDEX idx_identifier (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Platform İstatistikleri (Genel Kullanım İstatistikleri)
CREATE TABLE IF NOT EXISTS platform_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL UNIQUE,
    total_views INT DEFAULT 0,
    last_viewed DATETIME NULL,
    success_count INT DEFAULT 0,
    fail_count INT DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Kullanıcı Oturumları (OAuth Login için - basit)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_user_id VARCHAR(255) NOT NULL UNIQUE,       -- Google/GitHub/Facebook ID
    provider VARCHAR(20) NOT NULL,                       -- google, github, facebook
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    picture_url VARCHAR(1000) NULL,
    last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 1                        -- şimdilik herkes admin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Medya İçerikleri (Postlar, Thumbnail'lar vb. - isteğe bağlı detaylı kayıt)
CREATE TABLE IF NOT EXISTS profile_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,                             -- recent_profiles.id'ye referans
    media_type ENUM('post', 'highlight', 'cover', 'thumbnail') NOT NULL,
    media_url VARCHAR(1000) NOT NULL,
    thumbnail_url VARCHAR(1000) NULL,
    caption TEXT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES recent_profiles(id) ON DELETE CASCADE,
    INDEX idx_profile_media (profile_id, media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Hata Logları (Scraping hatalarını takip için)
CREATE TABLE IF NOT EXISTS scrape_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_url VARCHAR(1000) NOT NULL,
    platform VARCHAR(50) NOT NULL,
    error_message TEXT NULL,
    http_code INT NULL,
    method_used VARCHAR(100) NULL,
    logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logged_at (logged_at),
    INDEX idx_platform_error (platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Dashboard Ayarları (İsteğe bağlı - global ayarlar)
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek başlangıç ayarları (isteğe bağlı ekleyebilirsin)
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('recent_profiles_limit', '12'),
('site_title', 'Sosyal Medya Profil Görüntüleyici'),
('enable_registration', '0');
   ```
4. Siteye girin → "Dashboard'a Giriş Yap" → Google/GitHub/Facebook ile giriş yapın
5. Dashboard'dan platformları düzenleyin → **Generate Scraper** yapın

## 🎨 Tema Özelleştirme

Dashboard → "Tema & Stil Düzenleyici" bölümünden kendi CSS'inizi yazın. Değişiklikler anında geçerli olur!

## 🔧 Gelişmiş: Resmi API'lerle Daha Stabil Erişim

InfinityFree'de Composer çalışmasa bile:

1. Yerel bilgisayarınızda çalıştırın:
   ```bash
   composer require guzzlehttp/guzzle
   composer require league/oauth2-facebook
   # vb.
   ```
2. Oluşan `vendor/` klasörünü FTP ile yükleyin
3. Artık `composer_api` türü yöntemler ekleyebilirsiniz!

## 🧠 Yapay Zeka ile Yeni Yöntem Geliştirme

Platform değiştiğinde:
1. Profili tarayıcıda aç → F12 → Network
2. Resim yüklenirken hangi istek atılıyor bak
3. Grok/ChatGPT'ye şu promptu ver:
   > "Instagram profil resmi şu endpoint'ten geliyor: https://i.instagram.com/api/v1/...  
   > Header: x-ig-app-id: 936619743392459  
   > JSON path: data.user.profile_pic_url_hd  
   > Bunu PHP cURL ile nasıl çekerim? s150x150 → s1080x1080 yap."

Alınan kodu dashboard'a ekleyin → Kaydet → Generate Scraper!

## 📂 Dosya Yapısı

```
/
├── index.php
├── dashboard.php
├── config.php
├── scraper/
│   ├── platforms.json
│   ├── scraper.php          ← otomatik üretilir
│   └── scraper.js           ← otomatik üretilir
├── engine/
│   ├── generate-scraper.php
│   └── auth/                ← OAuth dosyaları
├── assets/
│   ├── css/                 ← styles.css + notifications.css + media-gallery.css
│   ├── js/                  ← main.js, dashboard.js, auth.js vb.
│   └── templates/           ← header.php, footer.php, modals.php
├── cache/
└── logs/
```

## 🤝 Katkıda Bulunun

Yeni platform yöntemi buldunuz mu?  
`platforms.json`'u güncelleyin → Pull Request gönderin → herkes faydalansın!

## 📄 Lisans

GPL v2.0 License — özgürce kullanın, değiştirin, paylaşın.

## 💌 Teşekkürler

Bu proje @metatronslove ve Grok (xAI) işbirliğiyle ortaya çıktı.  
Sonsuz teşekkürler!

---

**⭐ Star vermeyi unutmayın!**  
Her star bir private profil resmi daha demek 😏
