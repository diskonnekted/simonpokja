/**
 * SIMONPOKJA Service Worker — Web Push Notifications
 * Standar W3C Push API
 */

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'SIMONPOKJA',
            body: event.data.text(),
            url: '/',
        };
    }

    const title = payload.title || 'Pemberitahuan SIMONPOKJA';
    const options = {
        body: payload.body || '',
        icon: payload.icon || '/favicon.ico',
        badge: payload.badge || '/favicon.ico',
        data: {
            url: payload.url || '/',
            id: payload.id || null,
        },
        tag: 'simonpokja-notif-' + (payload.id || Date.now()),
        renotify: true,
        requireInteraction: payload.tingkat === 'critical',
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Jika ada tab yang sudah terbuka, fokuskan tab tersebut dan arahkan ke URL
            for (let client of windowClients) {
                if ('focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            // Jika tidak ada tab terbuka, buka jendela baru
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
