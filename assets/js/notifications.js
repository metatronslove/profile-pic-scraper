// assets/js/notifications.js
// Notifications JavaScript File
class NotificationManager {
    constructor() {
        this.container = document.getElementById('notificationContainer');
        this.maxNotifications = 3;
        this.notifications = [];
        this.autoRemoveTime = 5000; // 5 seconds
        this.init();
    }
    
    init() {
        // Mevcut notification'ları yükle
        this.loadExistingNotifications();
        
        // Auto-remove interval'ını başlat
        this.startAutoRemove();
        
        // Global fonksiyonları tanımla
        window.showNotification = this.show.bind(this);
        window.removeNotification = this.remove.bind(this);
        window.clearNotifications = this.clearAll.bind(this);
    }
    
    loadExistingNotifications() {
        if (!this.container) return;
        
        const notificationElements = this.container.querySelectorAll('.notification');
        notificationElements.forEach((element, index) => {
            this.notifications.push({
                element: element,
                timestamp: Date.now() - (index * 1000) // Simulate timing
            });
        });
    }
    
    show(message, type = 'info', duration = 5000) {
        if (!this.container) return;
        
        // Notification element oluştur
        const notification = this.createNotificationElement(message, type);
        
        // Container'a ekle
        this.container.insertBefore(notification, this.container.firstChild);
        
        // Listeye ekle
        this.notifications.unshift({
            element: notification,
            timestamp: Date.now()
        });
        
        // Limit kontrolü
        this.enforceLimit();
        
        // Opacity'leri güncelle
        this.updateOpacities();
        
        // Otomatik kaldırma
        if (duration > 0) {
            setTimeout(() => {
                this.removeNotificationElement(notification);
            }, duration);
        }
        
        // Animasyon
        this.animateIn(notification);
        
        return notification;
    }
    
    createNotificationElement(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const iconClass = this.getIconClass(type);
        const timeText = this.getTimeText();
        
        notification.innerHTML = `
            <div class="notification-content">
                <i class="notification-icon ${iconClass}"></i>
                <span class="notification-message">${this.escapeHtml(message)}</span>
                <span class="notification-time">${timeText}</span>
            </div>
            <button class="notification-close" onclick="removeNotification(this.parentElement)">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Progress bar ekle (opsiyonel)
        if (type !== 'error') {
            const progress = document.createElement('div');
            progress.className = 'notification-progress';
            notification.appendChild(progress);
        }
        
        return notification;
    }
    
    getIconClass(type) {
        const icons = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }
    
    getTimeText() {
        const now = new Date();
        return now.toLocaleTimeString('tr-TR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    animateIn(element) {
        element.style.animation = 'slideInRight 0.3s ease';
        element.style.opacity = '0';
        
        requestAnimationFrame(() => {
            element.style.opacity = '1';
        });
    }
    
    animateOut(element, callback) {
        element.style.animation = 'slideOutRight 0.3s ease';
        element.style.opacity = '1';
        
        setTimeout(() => {
            element.style.opacity = '0';
            if (callback) callback();
        }, 50);
        
        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
            if (callback) callback();
        }, 300);
    }
    
    remove(element) {
        const notificationElement = element.closest('.notification');
        if (!notificationElement) return;
        
        this.removeNotificationElement(notificationElement);
    }
    
    removeNotificationElement(element) {
        if (!element || !element.parentNode) return;
        
        this.animateOut(element, () => {
            // Listeden kaldır
            this.notifications = this.notifications.filter(n => n.element !== element);
            
            // Opacity'leri güncelle
            this.updateOpacities();
        });
    }
    
    enforceLimit() {
        if (this.notifications.length > this.maxNotifications) {
            const excess = this.notifications.slice(this.maxNotifications);
            excess.forEach(item => {
                this.removeNotificationElement(item.element);
            });
        }
    }
    
    updateOpacities() {
        this.notifications.forEach((notification, index) => {
            const element = notification.element;
            if (!element) return;
            
            // Son 3 notification için opacity ayarla
            if (index < this.maxNotifications) {
                let opacity = 1;
                if (this.notifications.length >= 3) {
                    if (index === 2) opacity = 0.5;    // 3. en eski
                    if (index === 1) opacity = 0.75;   // 2.
                    // 0. index (en yeni) tam opak
                }
                
                element.style.opacity = opacity;
                element.style.transform = index > 0 ? `translateY(${index * 5}px) scale(${1 - (index * 0.02)})` : '';
                element.style.zIndex = this.maxNotifications - index;
            } else {
                element.style.opacity = '0';
                element.style.transform = 'translateY(15px) scale(0.94)';
            }
        });
    }
    
    clearAll() {
        // Tüm notification'ları animasyonla kaldır
        this.notifications.forEach(item => {
            this.animateOut(item.element);
        });
        
        // Listeyi temizle
        this.notifications = [];
        
        // Session'ı temizlemek için backend'e istek gönder
        fetch(`${window.baseUrl}?action=clear_notifications`, { method: 'POST' })
            .catch(err => console.error('Notification temizleme hatası:', err));
    }
    
    startAutoRemove() {
        // Her saniye eski notification'ları kontrol et
        setInterval(() => {
            const now = Date.now();
            this.notifications.forEach((notification, index) => {
                // İlk 3'ten sonrakileri otomatik kaldır
                if (index >= this.maxNotifications) {
                    const age = now - notification.timestamp;
                    if (age > this.autoRemoveTime) {
                        this.removeNotificationElement(notification.element);
                    }
                }
            });
        }, 1000);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Utility fonksiyonları
    success(message, duration = 3000) {
        return this.show(message, 'success', duration);
    }
    
    error(message, duration = 5000) {
        return this.show(message, 'error', duration);
    }
    
    warning(message, duration = 4000) {
        return this.show(message, 'warning', duration);
    }
    
    info(message, duration = 3000) {
        return this.show(message, 'info', duration);
    }
    
    // Notification sayısını getir
    getCount() {
        return this.notifications.length;
    }
    
    // En son notification'ı getir
    getLatest() {
        return this.notifications[0];
    }
}

// Sayfa yüklendiğinde NotificationManager'ı başlat
document.addEventListener('DOMContentLoaded', function() {
    window.notificationManager = new NotificationManager();
    
    // Örnek notification'lar (test için)
    if (window.location.search.includes('debug=notifications')) {
        setTimeout(() => window.notificationManager.info('Test bildirimi 1'), 100);
        setTimeout(() => window.notificationManager.success('Test bildirimi 2'), 500);
        setTimeout(() => window.notificationManager.warning('Test bildirimi 3'), 1000);
        setTimeout(() => window.notificationManager.error('Test bildirimi 4'), 1500);
    }
});

// Global fonksiyonlar (backward compatibility için)
window.showNotification = function(message, type = 'info') {
    if (window.notificationManager) {
        return window.notificationManager.show(message, type);
    }
    console.log(`[${type.toUpperCase()}] ${message}`);
    return null;
};

window.removeNotification = function(element) {
    if (window.notificationManager) {
        return window.notificationManager.remove(element);
    }
};

window.clearNotifications = function() {
    if (window.notificationManager) {
        return window.notificationManager.clearAll();
    }
};
