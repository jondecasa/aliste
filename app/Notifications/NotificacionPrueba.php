<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NotificacionPrueba extends Notification
{
    public function __construct(private readonly string $mensaje)
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
            ->title('Notificación de prueba')
            ->body($this->mensaje)
            ->icon('/images/icons/icon-192.png');
    }
}
