<?php

namespace App\Listeners;

use App\Models\RegistroLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\Events\NotificationFailed;
use Throwable;

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
        try {
            $mensaje = $event->message->toArray();

            RegistroLog::create([
                'tipo' => RegistroLog::TIPO_ERROR,
                'origen' => 'notificaciones:push',
                'mensaje' => Str::limit(
                    'Fallo al enviar una notificación push ('.$event->report->getReason().'): '.($mensaje['title'] ?? 'sin título'),
                    1000
                ),
                'contexto' => [
                    'endpoint' => $event->report->getEndpoint(),
                    'suscripcion_caducada' => $event->report->isSubscriptionExpired(),
                    'respuesta' => Str::limit((string) $event->report->getResponseContent(), 2000),
                    'titulo' => $mensaje['title'] ?? null,
                    'cuerpo' => $mensaje['body'] ?? null,
                ],
            ]);
        } catch (Throwable $errorAlRegistrar) {
            // Este listener observa un fallo de notificación; bajo ningún
            // concepto debe poder romper el flujo que lo disparó (p. ej. un
            // registro de usuario) si el propio registro del log falla.
            Log::error('No se pudo registrar un fallo de notificación push: '.$errorAlRegistrar->getMessage());
        }
    }
}
