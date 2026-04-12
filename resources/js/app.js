import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// ── Laravel Echo + Reverb ───────────────────────────────────────────────
// Reverb is the self-hosted WebSocket server bundled with Laravel.
// Echo uses it to subscribe to broadcast channels declared in routes/channels.php.
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster:    'reverb',
    key:            import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:         import.meta.env.VITE_REVERB_HOST,
    wsPort:         import.meta.env.VITE_REVERB_PORT     ?? 8080,
    wssPort:        import.meta.env.VITE_REVERB_PORT     ?? 443,
    forceTLS:      (import.meta.env.VITE_REVERB_SCHEME   ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    // Suppress Pusher's verbose logging in production
    disableStats: true,
});

// Expose for inline Blade scripts that do not use modules
window.Echo = window.Echo;
