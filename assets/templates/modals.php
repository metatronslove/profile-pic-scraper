<?php
// assets/templates/modals.php
// Tüm modallar — orijinal CSS'lerle tam uyumlu, responsive ve ortalanmış
?>

<!-- 1. Login Modal (OAuth Giriş) -->
<div id="loginModal" class="modal">
    <div class="modal-content" style="max-width: 500px; width: 90%;">
        <span class="close" onclick="closeLoginModal()">❎</span>
        
        <div class="modal-header" style="background: var(--primary); padding: 1.5rem; text-align: center; border-radius: var(--border-radius) var(--border-radius) 0 0;">
            <h2 style="color: white; margin: 0; font-size: 1.8rem;">
                <i class="fas fa-lock" style="margin-right: 10px;"></i>
                Dashboard Girişi
            </h2>
        </div>
        
        <div class="modal-body" style="padding: 2.5rem 2rem; text-align: center;">
            <p style="font-size: 1.1rem; color: var(--gray); margin-bottom: 2rem;">
                Ayarları yönetmek için lütfen bir hesapla giriş yapın.
            </p>
            
            <div class="oauth-buttons" style="display: flex; flex-direction: column; gap: 1rem; margin: 2rem 0;">
                <a href="<?= BASE_URL ?>engine/auth/google.php" class="btn-action" style="background: #db4437; padding: 1rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 12px; text-decoration: none;">
                    <i class="fab fa-google" style="font-size: 1.3rem;"></i>
                    Google ile Giriş Yap
                </a>
                
                <a href="<?= BASE_URL ?>engine/auth/github.php" class="btn-action" style="background: #333; padding: 1rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 12px; text-decoration: none;">
                    <i class="fab fa-github" style="font-size: 1.3rem;"></i>
                    GitHub ile Giriş Yap
                </a>
                
                <a href="<?= BASE_URL ?>engine/auth/facebook.php" class="btn-action" style="background: #1877f2; padding: 1rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 12px; text-decoration: none;">
                    <i class="fab fa-facebook-f" style="font-size: 1.3rem;"></i>
                    Facebook ile Giriş Yap
                </a>
            </div>
            
            <p style="font-size: 0.9rem; color: var(--gray-light); margin-top: 2rem;">
                <i class="fas fa-info-circle"></i>
                Verileriniz sadece oturum açma için kullanılır · Hiçbir bilgi saklanmaz
            </p>
        </div>
    </div>
</div>

<!-- 2. Medya Görüntüleyici Modal -->
<div id="mediaViewerModal" class="modal">
    <div class="modal-content large" style="max-width: 950px; width: 95%;">
        <span class="close" onclick="closeMediaViewer()">❎</span>
        <div class="modal-body" style="padding: 0; text-align: center; background: #000;">
            <img id="mediaViewerImage" src="" alt="Büyük Görüntü" class="modal-image" style="max-width: 100%; max-height: 75vh; object-fit: contain;">
        </div>
        <div class="modal-footer" style="padding: 1.5rem; background: var(--light); display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <button onclick="downloadCurrentMedia()" class="btn-modal">
                <i class="fas fa-download"></i> İndir
            </button>
            <button onclick="copyCurrentMediaLink()" class="btn-modal" style="background: var(--info);">
                <i class="fas fa-copy"></i> Linki Kopyala
            </button>
            <button onclick="openInNewTab()" class="btn-modal" style="background: var(--dark-light);">
                <i class="fas fa-external-link-alt"></i> Yeni Sekmede Aç
            </button>
        </div>
    </div>
</div>

<!-- 3. Genel Onay Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content" style="max-width: 500px; width: 90%;">
        <span class="close" onclick="closeConfirmModal()">❎</span>
        <div class="modal-body" style="padding: 2rem; text-align: center;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning); margin-bottom: 1rem;"></i>
            <h3 id="confirmTitle" style="margin: 1rem 0;">Onay Gerekiyor</h3>
            <p id="confirmMessage" style="font-size: 1.1rem; color: var(--gray);">Emin misiniz?</p>
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                <button onclick="confirmModalNo()" class="btn-action secondary">İptal</button>
                <button onclick="confirmModalYes()" class="btn-action">Evet, Devam Et</button>
            </div>
        </div>
    </div>
</div>

<script>
// Modal Fonksiyonları (orijinal uyumlu)
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'flex';
}
function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
}

// Medya Modal
let currentMediaUrl = '';
function viewMedia(url) {
    currentMediaUrl = url;
    document.getElementById('mediaViewerImage').src = url;
    document.getElementById('mediaViewerModal').style.display = 'flex';
}
function closeMediaViewer() {
    document.getElementById('mediaViewerModal').style.display = 'none';
}
function downloadCurrentMedia() {
    const a = document.createElement('a');
    a.href = currentMediaUrl;
    a.download = '';
    a.click();
}
function copyCurrentMediaLink() {
    navigator.clipboard.writeText(currentMediaUrl);
    showNotification('Link panoya kopyalandı!', 'success');
}
function openInNewTab() {
    window.open(currentMediaUrl, '_blank');
}

// Onay Modal
let confirmCallback = null;
function openConfirmModal(title, message, callback) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    confirmCallback = callback;
    document.getElementById('confirmModal').style.display = 'flex';
}
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
function confirmModalYes() {
    closeConfirmModal();
    if (confirmCallback) confirmCallback();
}
function confirmModalNo() {
    closeConfirmModal();
}

// Modal dışına tıklayınca kapat
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
</script>

<style>
/* Orijinal CSS'lerle tam uyumlu ek ayarlar */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-content {
    position: relative;
    animation: scaleIn 0.3s ease;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.modal.large .modal-content {
    max-width: 950px;
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 2rem;
    color: white;
    cursor: pointer;
    z-index: 10;
    opacity: 0.8;
    transition: opacity 0.3s;
}

.close:hover {
    opacity: 1;
}

.oauth-buttons a {
    text-decoration: none;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.oauth-buttons a:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}
</style>
