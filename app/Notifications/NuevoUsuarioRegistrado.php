<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NuevoUsuarioRegistrado extends Notification
{
    public function __construct(private readonly User $usuario)
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
        return (new WebPushMessage())
            ->title('Nuevo usuario registrado')
            ->body($this->cuerpo())
            ->icon('/images/icons/icon-192.png');
    }

    private function cuerpo(): string
    {
        $pueblo = $this->usuario->pueblo?->nombre;

        return $pueblo
            ? "{$this->usuario->name} de {$pueblo} se ha registrado"
            : "{$this->usuario->name} se ha registrado";
    }
}
