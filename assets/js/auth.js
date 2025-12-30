// assets/js/auth.js
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'block';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
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
