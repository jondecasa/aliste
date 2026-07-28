<?php

namespace App\Listeners;

use App\Models\RegistroLog;
use App\Models\User;
use App\Notifications\NuevoUsuarioRegistrado;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class NotificarAdminNuevoRegistro
{
    public function handle(Registered $event): void
    {
        // DIAGNÓSTICO TEMPORAL: registrar cada invocación de este listener
        // para averiguar por qué se dispara dos veces en producción vía web,
        // pero no al llamar a Notification::send() directamente. Quitar en
        // cuanto se resuelva el problema de duplicados.
        RegistroLog::create([
            'tipo' => RegistroLog::TIPO_INFORMACION,
            'origen' => 'diagnostico:notificar-admin-nuevo-registro',
            'mensaje' => 'handle() invocado para usuario '.($event->user->email ?? '?'),
            'contexto' => [
                'id_invocacion' => Str::uuid()->toString(),
                'microtime' => microtime(true),
                'pid' => getmypid(),
                'usuario_id' => $event->user->id ?? null,
            ],
        ]);

        $usuario = $event->user;

        if (! $usuario instanceof User) {
            return;
        }

        $administradores = User::where('rol', User::ROL_ADMINISTRADOR)
            ->whereHas('pushSubscriptions')
            ->get();

        if ($administradores->isEmpty()) {
            return;
        }

        Notification::send($administradores, new NuevoUsuarioRegistrado($usuario));
    }
}
