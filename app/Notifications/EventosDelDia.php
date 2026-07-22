<?php

namespace App\Notifications;

use App\Models\Evento;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class EventosDelDia extends Notification
{
    /**
     * @param  Collection<int, Evento>  $eventos
     */
    public function __construct(private readonly Collection $eventos)
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
        $titulo = $this->eventos->count() === 1
            ? 'Hoy hay 1 evento en la comarca'
            : "Hoy hay {$this->eventos->count()} eventos en la comarca";

        $cuerpo = $this->eventos
            ->map(fn (Evento $evento) => "{$evento->titulo} ({$evento->pueblo->nombre})")
            ->take(3)
            ->implode(' · ');

        if ($this->eventos->count() > 3) {
            $cuerpo .= '…';
        }

        return (new WebPushMessage())
            ->title($titulo)
            ->body($cuerpo)
            ->icon('/images/icons/icon-192.png')
            ->data(['url' => route('inicio')]);
    }
}
