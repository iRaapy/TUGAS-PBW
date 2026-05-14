import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
Pusher.logToConsole = true;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key:         import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:      import.meta.env.VITE_REVERB_HOST,
    wsPort:      import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort:     import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS:    (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

// Log saat koneksi berhasil
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ WebSocket terhubung ke Reverb');
});

// Log semua event yang masuk
window.Echo.connector.pusher.bind_global((eventName, data) => {
    console.log('📨 Event masuk:', eventName, data);
});