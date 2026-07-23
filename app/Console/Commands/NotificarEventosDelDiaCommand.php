<?php

namespace App\Console\Commands;

use App\Models\Evento;
use App\Models\Pueblo;
use App\Models\User;
use App\Notifications\EventosMiPueblo;
use App\Notifications\EventosPrincipalesOtrosPueblos;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class NotificarEventosDelDiaCommand extends Command
{
    protected $signature = 'notificaciones:eventos-del-dia';

    protected $description = 'Envía notificaciones push con los eventos de la comarca de hoy';

    public function handle(): int
    {
        $hoy = now()->toDateString();

        $eventosHoy = Evento::query()
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

        if ($eventosHoy->isEmpty()) {
            $this->info('No hay eventos hoy, no se envía ninguna notificación.');

            return self::SUCCESS;
        }

        $this->notificarEventosPrincipalesOtrosPueblos($eventosHoy);
        $this->notificarEventosDeMiPueblo($eventosHoy);

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Evento>  $eventosHoy
     */
    private function notificarEventosPrincipalesOtrosPueblos(Collection $eventosHoy): void
    {
        $eventosPrincipales = $eventosHoy->where('es_principal', true);

        if ($eventosPrincipales->isEmpty()) {
            $this->info('No hay eventos principales hoy, no se envía la notificación de otros pueblos.');

            return;
        }

        $usuariosPorPueblo = User::whereHas('pushSubscriptions')
            ->where('notif_eventos_otros_pueblos', true)
            ->get()
            ->groupBy('pueblo_id');

        $enviados = 0;

        foreach ($usuariosPorPueblo as $puebloId => $usuariosDelGrupo) {
            $eventos = $puebloId
                ? $eventosPrincipales->where('pueblo_id', '!=', $puebloId)->values()
                : $eventosPrincipales->values();

            if ($eventos->isEmpty()) {
                continue;
            }

            Notification::send($usuariosDelGrupo, new EventosPrincipalesOtrosPueblos($eventos));
            $enviados += $usuariosDelGrupo->count();
        }

        $this->info("Notificados {$enviados} usuarios sobre eventos principales de otros pueblos.");
    }

    /**
     * @param  Collection<int, Evento>  $eventosHoy
     */
    private function notificarEventosDeMiPueblo(Collection $eventosHoy): void
    {
        $enviados = 0;

        foreach ($eventosHoy->groupBy('pueblo_id') as $puebloId => $eventosDelPueblo) {
            $usuarios = User::where('pueblo_id', $puebloId)
                ->whereHas('pushSubscriptions')
                ->where('notif_eventos_mi_pueblo', true)
                ->get();

            if ($usuarios->isEmpty()) {
                continue;
            }

            $pueblo = $eventosDelPueblo->first()->pueblo ?? Pueblo::find($puebloId);

            Notification::send($usuarios, new EventosMiPueblo($pueblo, $eventosDelPueblo->values()));
            $enviados += $usuarios->count();
        }

        $this->info("Notificados {$enviados} usuarios sobre eventos de su propio pueblo.");
    }
}
