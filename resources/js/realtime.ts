import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echo: Echo<'reverb'> | null = null;

export function connectRealtime(token: string): void {
  if (echo || !import.meta.env.VITE_REVERB_APP_KEY) return;

  window.Pusher = Pusher;
  echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: { headers: { Authorization: `Bearer ${token}` } },
  });
}

export function getEcho(): Echo<'reverb'> | null {
  return echo;
}

export function disconnectRealtime(): void {
  echo?.disconnect();
  echo = null;
}
