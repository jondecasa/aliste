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

        $administradores = User::where('rol', User::ROL_ADMINISTRADOR)
            ->whereHas('pushSubscriptions')
            ->get();

        if ($administradores->isEmpty()) {
            return;
        }

        Notification::send($administradores, new NuevoUsuarioRegistrado($usuario));
    }
}
