<?php

namespace App\Console\Commands;

use App\Models\RegistroLog;
use App\Models\User;
use App\Notifications\NotificacionPrueba;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Comando de diagnóstico manual: envía una notificación push de prueba solo
 * a los administradores. NO está enganchado a routes/console.php a
 * propósito — no debe ejecutarse nunca de forma programada, solo cuando un
 * administrador lo lance a mano por SSH en producción para comprobar que
 * las notificaciones push funcionan.
 */
class NotificarPruebaAdminCommand extends Command
{
    protected $signature = 'notificaciones:prueba-admin {--mensaje= : Texto de la notificación de prueba}';

    protected $description = 'Envía una notificación push de prueba solo a los administradores (uso manual, no programado)';

    public function handle(): int
    {
        $comando = 'notificaciones:prueba-admin';
        $mensaje = $this->option('mensaje') ?: 'Esta es una notificación de prueba enviada manualmente desde el servidor.';

        $administradores = User::where('rol', User::ROL_ADMINISTRADOR)
            ->whereHas('pushSubscriptions')
            ->get();

        if ($administradores->isEmpty()) {
            $this->warn('No hay administradores con suscripción push activa. No se ha enviado nada.');

            RegistroLog::registrarTareaProgramada($comando, exito: true, contexto: [
                'administradores_notificados' => 0,
            ]);

            return self::SUCCESS;
        }

        try {
            Notification::send($administradores, new NotificacionPrueba($mensaje));
        } catch (Throwable $e) {
            $this->error('No se pudo enviar la notificación de prueba: '.$e->getMessage());

            RegistroLog::registrarTareaProgramada($comando, exito: false, contexto: [
                'administradores' => $administradores->count(),
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }

        $this->info("Notificación de prueba enviada a {$administradores->count()} administrador(es).");

        RegistroLog::registrarTareaProgramada($comando, exito: true, contexto: [
            'administradores_notificados' => $administradores->count(),
            'mensaje' => $mensaje,
        ]);

        return self::SUCCESS;
    }
}
