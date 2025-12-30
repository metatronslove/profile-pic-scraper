<?php
// assets/templates/modals.php
// Tüm modallar burada tanımlı — orijinal CSS'lerle tam uyumlu
?>

<!-- 1. Login Modal (OAuth Giriş) -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLoginModal()">&times;</span>
        <div class="modal-header" style="background: var(--primary);">
            <h2 style="color: white; margin: 0;">Giriş Yap</h2>
        </div>
        <div class="modal-body" style="text-align: center; padding: 2rem;">
            <p>Dashboard'a erişmek için bir hesapla giriş yapın.</p>
            
            <div class="oauth-buttons" style="display: flex; flex-direction: column; gap: 15px; margin: 30px 0;">
                <a href="<?= BASE_URL ?>engine/auth/google.php" class="btn-action" style="background: #db4437; justify-content: center;">
                    <i class="fab fa-google"></i> Google ile Giriş Yap
                </a>
                <a href="<?= BASE_URL ?>engine/auth/github.php" class="btn-action" style="background: #333; justify-content: center;">
                    <i class="fab fa-github"></i> GitHub ile Giriş Yap
                </a>
                <a href="<?= BASE_URL ?>engine/auth/facebook.php" class="btn-action" style="background: #1877f2; justify-content: center;">
                    <i class="fab fa-facebook-f"></i> Facebook ile Giriş Yap
                </a>
            </div>
            
            <p style="font-size: 0.9rem; color: var(--gray);">
                Verileriniz sadece oturum açma için kullanılır · Hiçbir bilgi saklanmaz
            </p>
        </div>
    </div>
</div>

<!-- 2. Medya Görüntüleyici Modal (index.php'den gallery için) -->
<div id="mediaViewerModal" class="modal">
    <div class="modal-content large">
        <span class="close" onclick="closeMediaViewer()">&times;</span>
        <div class="modal-body" style="padding: 0; text-align: center;">
            <img id="mediaViewerImage" src="" alt="Büyük Görüntü" class="modal-image" style="max-width: 100%; max-height: 70vh; border-radius: 0 0 12px 12px;">
            
            <div class="modal-footer" style="padding: 1.5rem; background: var(--light);">
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
</div>

<!-- 3. Genel Bildirim / Onay Modal (İsteğe Bağlı Kullanım) -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeConfirmModal()">&times;</span>
        <div class="modal-body">
            <h3 id="confirmTitle">Onay Gerekiyor</h3>
            <p id="confirmMessage">Emin misiniz?</p>
            <div style="text-align: right; margin-top: 30px;">
                <button onclick="confirmModalNo()" class="btn-action secondary">İptal</button>
                <button onclick="confirmModalYes()" class="btn-action">Evet, Devam Et</button>
            </div>
        </div>
    </div>
</div>

<script>
// Login Modal
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'flex';
}
function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
}

// Medya Görüntüleyici
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
    const link = document.createElement('a');
    link.href = currentMediaUrl;
    link.download = '';
    link.click();
}
function copyCurrentMediaLink() {
    navigator.clipboard.writeText(currentMediaUrl);
    showNotification('Link panoya kopyalandı!', 'success');
}
function openInNewTab() {
    window.open(currentMediaUrl, '_blank');
}

// Genel Onay Modal
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
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}
</script>

<style>
/* Modal genel stilleri zaten styles.css'te var, sadece flex için ek */
.modal {
    display: none;
    align-items: center;
    justify-content: center;
}

.modal.large .modal-content {
    max-width: 900px;
}

.oauth-buttons a {
    text-decoration: none;
    font-weight: 600;
}
</style>
