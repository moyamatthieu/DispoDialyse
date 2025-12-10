import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configuration CSRF Token
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token non trouvé: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Configuration Laravel Echo pour WebSocket (Reverb/Pusher)
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Initialisation Echo si configuré
if (import.meta.env.VITE_PUSHER_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        wsHost: import.meta.env.VITE_PUSHER_HOST ?? 'localhost',
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 8080,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 8080,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    });
}