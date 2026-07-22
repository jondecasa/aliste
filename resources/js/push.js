function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);

    return Uint8Array.from([...rawData].map((caracter) => caracter.charCodeAt(0)));
}

function cabecerasJson() {
    return {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
    };
}

async function registrarServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    return navigator.serviceWorker.register('/sw.js');
}

async function estadoSuscripcion() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return false;
    }

    const registro = await navigator.serviceWorker.getRegistration();

    if (!registro) {
        return false;
    }

    const suscripcion = await registro.pushManager.getSubscription();

    return !!suscripcion;
}

async function suscribirNotificaciones(vapidPublicKey) {
    const registro = await registrarServiceWorker();

    if (!registro) {
        throw new Error('Este navegador no soporta notificaciones push.');
    }

    const permiso = await Notification.requestPermission();

    if (permiso !== 'granted') {
        throw new Error('Has denegado el permiso de notificaciones.');
    }

    const suscripcion = await registro.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
    });

    const respuesta = await fetch('/push/suscribirse', {
        method: 'POST',
        headers: cabecerasJson(),
        body: JSON.stringify(suscripcion),
    });

    if (!respuesta.ok) {
        throw new Error('No se pudo guardar la suscripción en el servidor.');
    }

    return suscripcion;
}

async function desuscribirNotificaciones() {
    const registro = await navigator.serviceWorker.getRegistration();

    if (!registro) {
        return;
    }

    const suscripcion = await registro.pushManager.getSubscription();

    if (!suscripcion) {
        return;
    }

    await fetch('/push/desuscribirse', {
        method: 'POST',
        headers: cabecerasJson(),
        body: JSON.stringify({ endpoint: suscripcion.endpoint }),
    });

    await suscripcion.unsubscribe();
}

window.PushNotificaciones = {
    estadoSuscripcion,
    suscribirNotificaciones,
    desuscribirNotificaciones,
};

registrarServiceWorker();
