/* Kaan — service worker for Web Push (PWA). */

self.addEventListener('push', function (event) {
    var data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) { data = { body: event.data && event.data.text() }; }

    var title = data.title || 'Kaan';
    var options = {
        body: data.body || '',
        icon: '/uploads/app-icon-192.png',
        badge: '/uploads/app-badge.png',
        data: { url: data.url || '/notificaciones' },
        tag: data.tag || undefined,
        renotify: !! data.tag
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) || '/notificaciones';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
            for (var i = 0; i < list.length; i++) {
                var c = list[i];
                if ('focus' in c) {
                    c.navigate(url);
                    return c.focus();
                }
            }
            if (clients.openWindow) { return clients.openWindow(url); }
        })
    );
});
