// assets/js/auth.js
function openLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Dashboard erişim kontrolü
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('dashboard.php')) {
        // Oturum kontrolü (basit)
        fetch('engine/auth/check.php')
            .then(r => r.json())
            .then(data => {
                if (!data.logged_in) {
                    openLoginModal();
                }
            });
    }
});
