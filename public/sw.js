self.addEventListener('install', (evento) => {
    self.skipWaiting();
});

self.addEventListener('activate', (evento) => {
    evento.waitUntil(self.clients.claim());
});

self.addEventListener('push', (evento) => {
    if (!evento.data) {
        return;
    }

    const datos = evento.data.json();

    evento.waitUntil(
        self.registration.showNotification(datos.title, {
            body: datos.body,
            icon: datos.icon || '/images/icons/icon-192.png',
            badge: datos.badge || '/images/icons/icon-192.png',
            data: { url: datos.data?.url || '/' },
        })
    );
});

self.addEventListener('notificationclick', (evento) => {
    evento.notification.close();

    const url = evento.notification.data?.url || '/';

    evento.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((listaClientes) => {
            for (const cliente of listaClientes) {
                if (cliente.url === url && 'focus' in cliente) {
                    return cliente.focus();
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
