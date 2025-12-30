// assets/js/gallery.js
// Medya Galerisi İşlevleri
function downloadAllMedia() {
    const mediaItems = document.querySelectorAll('.media-item img');
    if (mediaItems.length === 0) {
        showNotification('İndirilecek medya bulunamadı', 'warning');
        return;
    }
    
    // Her resmi ayrı sekmede aç (geçici çözüm)
    mediaItems.forEach(img => {
        window.open(img.src, '_blank');
    });
    
    showNotification('Medya öğeleri yeni sekmelerde açılıyor. Sağ tıklayıp "Farklı Kaydet" diyerek indirebilirsiniz.', 'info');
}

function copyGalleryLinks() {
    const mediaItems = document.querySelectorAll('.media-item img');
    if (mediaItems.length === 0) {
        showNotification('Kopyalanacak link bulunamadı', 'warning');
        return;
    }
    
    const urls = Array.from(mediaItems).map(img => img.src);
    const text = urls.join('\n');
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Tüm medya linkleri panoya kopyalandı!', 'success');
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
            showNotification('Tüm medya linkleri panoya kopyalandı!', 'success');
        } catch (err) {
            showNotification('Kopyalama başarısız!', 'error');
        }
        document.body.removeChild(textArea);
    }
}

// Medya filtreleme (isteğe bağlı)
function filterMedia(type) {
    const allItems = document.querySelectorAll('.media-item');
    allItems.forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}
