<?php

namespace App\Listeners;

use App\Models\RegistroLog;
use NotificationChannels\WebPush\Events\NotificationFailed;

/**
 * NotificationChannels\WebPush\WebPushChannel no lanza excepciones cuando el
 * envío falla (suscripción caducada, claves VAPID incorrectas, etc.): solo
 * dispara este evento. Sin este listener, esos fallos son invisibles — no
 * aparecen ni en /admin/logs ni en ningún otro sitio.
 */
class RegistrarFalloNotificacionPush
{
    public function handle(NotificationFailed $event): void
    {
        $mensaje = $event->message->toArray();

        RegistroLog::create([
            'tipo' => RegistroLog::TIPO_ERROR,
            'origen' => 'notificaciones:push',
            'mensaje' => 'Fallo al enviar una notificación push ('.$event->report->getReason().'): '.($mensaje['title'] ?? 'sin título'),
            'contexto' => [
                'endpoint' => $event->report->getEndpoint(),
                'suscripcion_caducada' => $event->report->isSubscriptionExpired(),
                'respuesta' => $event->report->getResponseContent(),
                'titulo' => $mensaje['title'] ?? null,
                'cuerpo' => $mensaje['body'] ?? null,
            ],
        ]);
    }
}
