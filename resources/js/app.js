import './bootstrap';
import Alpine from 'alpinejs';

// Initialisation Alpine.js
window.Alpine = Alpine;

// Composants Alpine.js réutilisables
Alpine.data('sidebar', () => ({
    open: window.innerWidth >= 1024,
    
    toggle() {
        this.open = !this.open;
    },
    
    init() {
        // Adapter la sidebar selon la taille d'écran
        window.addEventListener('resize', () => {
            this.open = window.innerWidth >= 1024;
        });
    }
}));

Alpine.data('modal', (initialOpen = false) => ({
    open: initialOpen,
    
    show() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    
    hide() {
        this.open = false;
        document.body.style.overflow = '';
    },
    
    toggle() {
        if (this.open) {
            this.hide();
        } else {
            this.show();
        }
    }
}));

Alpine.data('dropdown', () => ({
    open: false,
    
    toggle() {
        this.open = !this.open;
    },
    
    close() {
        this.open = false;
    }
}));

Alpine.data('toast', () => ({
    show: false,
    message: '',
    type: 'success',
    
    display(message, type = 'success', duration = 3000) {
        this.message = message;
        this.type = type;
        this.show = true;
        
        setTimeout(() => {
            this.show = false;
        }, duration);
    }
}));

Alpine.data('tabs', (defaultTab = 0) => ({
    activeTab: defaultTab,
    
    setTab(index) {
        this.activeTab = index;
    },
    
    isActive(index) {
        return this.activeTab === index;
    }
}));

Alpine.data('search', () => ({
    query: '',
    results: [],
    loading: false,
    
    async search(url) {
        if (this.query.length < 2) {
            this.results = [];
            return;
        }
        
        this.loading = true;
        
        try {
            const response = await fetch(`${url}?q=${encodeURIComponent(this.query)}`);
            this.results = await response.json();
        } catch (error) {
            console.error('Erreur de recherche:', error);
        } finally {
            this.loading = false;
        }
    },
    
    clear() {
        this.query = '';
        this.results = [];
    }
}));

Alpine.data('notifications', () => ({
    notifications: [],
    unreadCount: 0,
    
    init() {
        // Charger les notifications initiales
        this.fetchNotifications();
        
        // Écouter les nouvelles notifications (WebSocket)
        if (window.Echo) {
            window.Echo.private(`user.${window.userId}`)
                .listen('NewNotification', (e) => {
                    this.addNotification(e.notification);
                });
        }
    },
    
    async fetchNotifications() {
        try {
            const response = await fetch('/api/notifications');
            const data = await response.json();
            this.notifications = data.notifications;
            this.unreadCount = data.unread_count;
        } catch (error) {
            console.error('Erreur chargement notifications:', error);
        }
    },
    
    addNotification(notification) {
        this.notifications.unshift(notification);
        this.unreadCount++;
    },
    
    async markAsRead(notificationId) {
        try {
            await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const notification = this.notifications.find(n => n.id === notificationId);
            if (notification && !notification.read_at) {
                notification.read_at = new Date().toISOString();
                this.unreadCount--;
            }
        } catch (error) {
            console.error('Erreur marquage notification:', error);
        }
    },
    
    async markAllAsRead() {
        try {
            await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            this.notifications.forEach(n => {
                n.read_at = new Date().toISOString();
            });
            this.unreadCount = 0;
        } catch (error) {
            console.error('Erreur marquage notifications:', error);
        }
    }
}));

Alpine.data('confirmDialog', () => ({
    show: false,
    message: '',
    confirmCallback: null,
    
    confirm(message, callback) {
        this.message = message;
        this.confirmCallback = callback;
        this.show = true;
    },
    
    accept() {
        if (this.confirmCallback) {
            this.confirmCallback();
        }
        this.cancel();
    },
    
    cancel() {
        this.show = false;
        this.message = '';
        this.confirmCallback = null;
    }
}));

// Directives personnalisées Alpine.js
Alpine.directive('click-outside', (el, { expression }, { evaluate }) => {
    const handler = (e) => {
        if (!el.contains(e.target)) {
            evaluate(expression);
        }
    };
    
    document.addEventListener('click', handler);
    
    return () => {
        document.removeEventListener('click', handler);
    };
});

// Helpers globaux
window.formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

window.formatTime = (date) => {
    return new Date(date).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

window.formatDateTime = (date) => {
    return `${formatDate(date)} à ${formatTime(date)}`;
};

// Démarrer Alpine.js
Alpine.start();

// Gestion des erreurs globales
window.addEventListener('error', (event) => {
    console.error('Erreur globale:', event.error);
});

// Debug mode en développement
if (import.meta.env.DEV) {
    console.log('DispoDialyse - Mode développement activé');
}