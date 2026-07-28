<?php

namespace Tests\Feature;

use App\Models\RegistroLog;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Minishlink\WebPush\MessageSentReport;
use NotificationChannels\WebPush\Events\NotificationFailed;
use NotificationChannels\WebPush\PushSubscription;
use NotificationChannels\WebPush\WebPushMessage;
use Tests\TestCase;

class RegistrarFalloNotificacionPushTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_fallo_de_envio_push_se_registra_en_los_logs(): void
    {
        $usuario = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $suscripcion = $usuario->pushSubscriptions()->create([
            'endpoint' => 'https://push.example.com/caducada',
            'public_key' => 'clave-publica',
            'auth_token' => 'token-auth',
            'content_encoding' => 'aesgcm',
        ]);

        $peticion = new Request('POST', 'https://push.example.com/caducada');
        $respuesta = new Response(410, [], 'gone');

        $reporte = new MessageSentReport($peticion, $respuesta, success: false, reason: 'Subscription expired');

        $mensaje = (new WebPushMessage())->title('Nuevo usuario registrado')->body('Ana de Alcañices se ha registrado');

        event(new NotificationFailed($reporte, PushSubscription::find($suscripcion->id), $mensaje));

        $this->assertDatabaseHas('logs', [
            'tipo' => RegistroLog::TIPO_ERROR,
            'origen' => 'notificaciones:push',
        ]);

        $registro = RegistroLog::where('origen', 'notificaciones:push')->firstOrFail();

        $this->assertStringContainsString('Nuevo usuario registrado', $registro->mensaje);
        $this->assertTrue($registro->contexto['suscripcion_caducada']);
        $this->assertSame('https://push.example.com/caducada', $registro->contexto['endpoint']);
    }

    public function test_un_motivo_de_fallo_muy_largo_no_rompe_el_registro_del_log(): void
    {
        $usuario = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $suscripcion = $usuario->pushSubscriptions()->create([
            'endpoint' => 'https://push.example.com/motivo-largo',
            'public_key' => 'clave-publica',
            'auth_token' => 'token-auth',
            'content_encoding' => 'aesgcm',
        ]);

        $peticion = new Request('POST', 'https://push.example.com/motivo-largo');
        $respuesta = new Response(500, [], str_repeat('detalle del error del servicio push. ', 50));

        $motivoMuyLargo = str_repeat('Motivo de fallo extremadamente largo. ', 50);
        $reporte = new MessageSentReport($peticion, $respuesta, success: false, reason: $motivoMuyLargo);

        $mensaje = (new WebPushMessage())->title('Aviso')->body('Cuerpo');

        // Antes de esta corrección, esto lanzaba una excepción no capturada
        // (columna "mensaje" de logs demasiado corta), que rompía en cascada
        // cualquier flujo que disparara la notificación (p. ej. un registro).
        event(new NotificationFailed($reporte, PushSubscription::find($suscripcion->id), $mensaje));

        $this->assertDatabaseHas('logs', [
            'tipo' => RegistroLog::TIPO_ERROR,
            'origen' => 'notificaciones:push',
        ]);
    }
}
