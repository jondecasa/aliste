<?php

namespace App\Notifications;

use App\Models\Evento;
use App\Models\Pueblo;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class EventosMiPueblo extends Notification
{
    /**
     * @param  Collection<int, Evento>  $eventos
     */
    public function __construct(private readonly Pueblo $pueblo, private readonly Collection $eventos)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, self $notification): WebPushMessage
    {
        $titulo = "Hoy hay eventos en {$this->pueblo->nombre}";

        $cuerpo = $this->eventos
            ->map(fn (Evento $evento) => "{$evento->fecha_inicio->format('H:i')} - {$evento->titulo}")
            ->implode("\n");

        return (new WebPushMessage())
            ->title($titulo)
            ->body($cuerpo)
            ->icon('/images/icons/icon-192.png')
            ->data(['url' => route('pueblo.calendario', $this->pueblo)]);
    }
}
