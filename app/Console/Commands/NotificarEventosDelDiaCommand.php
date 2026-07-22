<?php

namespace App\Console\Commands;

use App\Models\Evento;
use App\Models\User;
use App\Notifications\EventosDelDia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotificarEventosDelDiaCommand extends Command
{
    protected $signature = 'notificaciones:eventos-del-dia';

    protected $description = 'Envía una notificación push con los eventos de la comarca de hoy';

    public function handle(): int
    {
        $hoy = now()->toDateString();

        $eventos = Evento::query()
            ->with('pueblo')
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereDate('fecha_fin', '>=', $hoy)
                    ->orWhere(function ($query) use ($hoy) {
                        $query->whereNull('fecha_fin')->whereDate('fecha_inicio', $hoy);
                    });
            })
            ->orderBy('fecha_inicio')
            ->get();

        if ($eventos->isEmpty()) {
            $this->info('No hay eventos hoy, no se envía notificación.');

            return self::SUCCESS;
        }

        $usuarios = User::whereHas('pushSubscriptions')->get();

        if ($usuarios->isEmpty()) {
            $this->info('No hay usuarios suscritos a notificaciones push.');

            return self::SUCCESS;
        }

        Notification::send($usuarios, new EventosDelDia($eventos));

        $this->info("Notificados {$usuarios->count()} usuarios sobre {$eventos->count()} eventos de hoy.");

        return self::SUCCESS;
    }
}
