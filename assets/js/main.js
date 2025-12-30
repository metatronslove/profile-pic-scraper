// assets/js/main.js - Tam Çalışan Sürüm
document.addEventListener('DOMContentLoaded', function() {
    // Grid düzenini güncelle
    updateGridColumns();
    
    // Quick link'leri ayarla
    setupQuickLinks();
    
    // Form submit animasyonu
    setupFormSubmit();
    
    // Enter tuşu desteği
    setupEnterKey();
    
    // Profil kartlarına hover efekti
    setupProfileCards();
    
    // Resim hatası yönetimi
    setupImageErrorHandling();
    
    // Responsive davranışlar
    setupResponsiveBehaviors();
    
    // URL parametresini kontrol et ve işle
    checkUrlParameter();
    
    // Event listener'ları ekle
    window.addEventListener('resize', updateGridColumns);
    const searchForm = document.getElementById('searchForm');
    const searchButton = document.getElementById('searchButton');
    
    if (searchForm && searchButton) {
        // Buton tıklaması
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            submitForm();
        });
        
        // Enter tuşu desteği
        document.getElementById('profileUrl').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitForm();
            }
        });
    }
    
    function submitForm() {
        const form = document.getElementById('searchForm');
        const button = document.getElementById('searchButton');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Buton durumunu güncelle
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';
        button.disabled = true;
        
        // Loading göster
        document.getElementById('loadingOverlay').style.display = 'flex';
        
        // Formu gönder
        form.submit();
        
        // 15 saniye sonra butonu eski haline getir
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.disabled = false;
            document.getElementById('loadingOverlay').style.display = 'none';
        }, 15000);
    }
});

// URL parametresini kontrol et
function checkUrlParameter() {
    const urlParams = new URLSearchParams(window.location.search);
    const url = urlParams.get('url');
    if (url) {
        // URL'yi input'a yerleştir
        document.getElementById('profileUrl').value = decodeURIComponent(url);
        // Opsiyonel: Otomatik submit (yorum satırını kaldırın)
        // document.getElementById('searchForm').submit();
    }
}

// Grid düzenini güncelle
function updateGridColumns() {
    const grid = document.getElementById('profilesGrid');
    if (!grid) return;
    
    const width = window.innerWidth;
    let columns;
    
    if (width >= 1400) columns = 5;
    else if (width >= 1200) columns = 4;
    else if (width >= 992) columns = 3;
    else if (width >= 768) columns = 2;
    else columns = 1;
    
    grid.style.gridTemplateColumns = `repeat(${columns}, minmax(200px, 1fr))`;
}

// Quick link'leri ayarla
function setupQuickLinks() {
    document.querySelectorAll('.quick-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.dataset.url;
            document.getElementById('profileUrl').value = url;
            
            // Formu submit et
            document.getElementById('searchForm').submit();
        });
    });
}

// Form submit animasyonu
function setupFormSubmit() {
    const form = document.getElementById('searchForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const button = this.querySelector('#searchButton');
        if (!button) return;
        
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';
        button.disabled = true;
        
        // Loading overlay göster
        document.getElementById('loadingOverlay').style.display = 'flex';
        
        // 30 saniye sonra eski haline getir
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.disabled = false;
            document.getElementById('loadingOverlay').style.display = 'none';
        }, 30000);
    });
}

// Enter tuşu desteği
function setupEnterKey() {
    const input = document.getElementById('profileUrl');
    if (!input) return;
    
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const button = document.getElementById('searchButton');
            if (button) button.click();
        }
    });
}

// Profil kartlarına hover efekti
function setupProfileCards() {
    document.querySelectorAll('.profile-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Resim hatası yönetimi
function setupImageErrorHandling() {
    document.querySelectorAll('img').forEach(img => {
        if (!img.hasAttribute('onerror')) {
            img.onerror = function() {
                handleImageError(this);
            };
        }
    });
}

// Yeni hata yönetim fonksiyonları
function handleImageError(img) {
    const platform = img.closest('.platform-header')?.className.match(/platform-(\w+)/)?.[1] || 'instagram';
    img.src = getPlaceholderImage(platform);
    img.style.opacity = '0.7';
}

function handleGalleryImageError(img, originalUrl) {
    // Önce base64 hatasıysa orijinal URL'yi dene
    if (img.src.startsWith('data:') && originalUrl) {
        img.src = originalUrl;
        img.onerror = function() {
            this.src = getPlaceholderImage('general');
            this.style.opacity = '0.5';
        };
    } else {
        img.src = getPlaceholderImage('general');
        img.style.opacity = '0.5';
    }
}

function getPlaceholderImage(platform) {
    const colors = {
        'instagram': 'E1306C',
        'youtube': 'FF0000',
        'facebook': '1877F2',
        'tiktok': '000000'
    };
    const color = colors[platform] || '666666';
    return `https://via.placeholder.com/400x400/${color}/ffffff?text=${platform.toUpperCase()}`;
}

// Tüm medyayı indir
function downloadAllMedia() {
    const mediaItems = document.querySelectorAll('.media-item img');
    if (mediaItems.length === 0) {
        showNotification('İndirilecek medya bulunamadı', 'warning');
        return;
    }
    
    showNotification(`${mediaItems.length} medya öğesi indiriliyor...`, 'info');
    
    // Her medya için ayrı indirme
    mediaItems.forEach((img, index) => {
        setTimeout(() => {
            try {
                const link = document.createElement('a');
                link.href = img.src;
                link.download = `media-${index + 1}-${Date.now()}.jpg`;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (e) {
                console.error(`Medya ${index + 1} indirme hatası:`, e);
            }
        }, index * 300); // 300ms aralıklarla
    });
}

// Galeri linklerini kopyala
function copyGalleryLinks() {
    const mediaItems = document.querySelectorAll('.media-item img');
    if (mediaItems.length === 0) {
        showNotification('Kopyalanacak link bulunamadı', 'warning');
        return;
    }
    
    const links = Array.from(mediaItems).map(img => {
        // data URL ise orijinal URL'yi al
        if (img.src.startsWith('data:')) {
            return img.getAttribute('data-original') || img.alt;
        }
        return img.src;
    }).filter(url => url && url !== 'null');
    
    const text = links.join('\n');
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification(`${links.length} link panoya kopyalandı!`, 'success');
        }).catch(err => {
            showNotification('Kopyalama başarısız!', 'error');
        });
    } else {
        // Fallback
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showNotification(`${links.length} link panoya kopyalandı!`, 'success');
        } catch (err) {
            showNotification('Kopyalama başarısız!', 'error');
        }
        document.body.removeChild(textArea);
    }
}

// Responsive davranışlar
function setupResponsiveBehaviors() {
    // Window resize için grid güncelleme (debounce ile)
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateGridColumns, 250);
    });
    
    // Mobil dokunma efektleri
    if ('ontouchstart' in window) {
        document.querySelectorAll('.profile-card, .btn-action, .quick-link').forEach(element => {
            element.style.cursor = 'pointer';
            element.addEventListener('touchstart', function() {
                this.style.opacity = '0.7';
            });
            element.addEventListener('touchend', function() {
                this.style.opacity = '1';
            });
        });
    }
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Global fonksiyonlar
window.downloadImage = function() {
    if (!window.currentProfileData || !window.currentProfileData.image_data) {
        showNotification('Resim indirilemiyor', 'error');
        return;
    }
    
    try {
        const link = document.createElement('a');
        const byteCharacters = atob(window.currentProfileData.image_data);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'image/jpeg' });
        const url = URL.createObjectURL(blob);
        
        link.href = url;
        link.download = `${window.currentProfileData.platform}-profile-${Date.now()}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        showNotification('Resim indiriliyor...', 'success');
    } catch (error) {
        console.error('İndirme hatası:', error);
        showNotification('İndirme başarısız!', 'error');
    }
};

window.copyToClipboard = function(text) {
    if (!navigator.clipboard) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showNotification('URL panoya kopyalandı!', 'success');
        } catch (err) {
            showNotification('Kopyalama başarısız!', 'error');
        }
        document.body.removeChild(textArea);
        return;
    }
    
    navigator.clipboard.writeText(text).then(() => {
        showNotification('URL panoya kopyalandı!', 'success');
    }).catch(err => {
        showNotification('Kopyalama başarısız!', 'error');
        console.error('Kopyalama hatası:', err);
    });
};

window.viewProfile = function(url) {
    // URL'yi parametre olarak ekle ve sayfayı yenile
    window.location.href = window.baseUrl + '?url=' + encodeURIComponent(url);
};

window.refreshGrid = function() {
    location.reload();
};

window.refreshResult = function() {
    if (window.currentProfileData) {
        window.location.href = window.baseUrl + '?url=' + encodeURIComponent(window.currentProfileData.original_url);
    }
};

window.shareProfile = function() {
    if (!window.currentProfileData) return;
    
    const shareData = {
        title: `${window.currentProfileData.platform} Profil Fotoğrafı`,
        text: 'Bu profil fotoğrafını görüntüle:',
        url: window.location.href
    };
    
    if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
        navigator.share(shareData)
            .then(() => showNotification('Paylaşıldı!', 'success'))
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Paylaşım hatası:', err);
                    copyToClipboard(window.location.href);
                }
            });
    } else {
        copyToClipboard(window.location.href);
    }
};

window.clearCache = function() {
    if (confirm('Önbellek temizlenecek. Emin misiniz?')) {
        fetch(`${window.baseUrl}?action=clear_cache`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Önbellek temizlendi!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Önbellek temizleme hatası!', 'error');
                }
            })
            .catch(error => {
                showNotification('Önbellek temizleme hatası!', 'error');
                console.error(error);
            });
    }
};
