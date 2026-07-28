<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NuevoUsuarioRegistrado;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;

class NotificarAdminNuevoRegistro
{
    public function handle(Registered $event): void
    {
        $usuario = $event->user;

        if (! $usuario instanceof User) {
            return;
        }

        // Guarda atómico: en producción se ha observado que este listener
        // puede dispararse más de una vez para el mismo evento dentro de una
        // misma petición (causa no determinada en Livewire). El UPDATE ...
        // WHERE NULL solo puede tener éxito una vez para un mismo usuario,
        // así que evita notificar por duplicado sin importar cuántas veces
        // se invoque handle().
        $marcado = User::where('id', $usuario->id)
            ->whereNull('notificacion_registro_enviada_en')
            ->update(['notificacion_registro_enviada_en' => now()]);

        if ($marcado === 0) {
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
