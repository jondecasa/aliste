<?php

namespace Tests\Feature;

use App\Models\RegistroLog;
use App\Models\User;
use App\Notifications\NotificacionPrueba;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificarPruebaAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifica_solo_a_administradores_con_suscripcion_push(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);
        $admin->pushSubscriptions()->create([
            'endpoint' => 'https://push.example.com/uno',
            'public_key' => 'clave-publica',
            'auth_token' => 'token-auth',
            'content_encoding' => 'aesgcm',
        ]);

        $adminSinSuscripcion = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $redactor = User::factory()->create(['rol' => User::ROL_REDACTOR]);
        $redactor->pushSubscriptions()->create([
            'endpoint' => 'https://push.example.com/dos',
            'public_key' => 'clave-publica',
            'auth_token' => 'token-auth',
            'content_encoding' => 'aesgcm',
        ]);

        $this->artisan('notificaciones:prueba-admin')->assertSuccessful();

        Notification::assertSentTo($admin, NotificacionPrueba::class);
        Notification::assertNotSentTo($adminSinSuscripcion, NotificacionPrueba::class);
        Notification::assertNotSentTo($redactor, NotificacionPrueba::class);

        $this->assertDatabaseHas('logs', [
            'tipo' => RegistroLog::TIPO_INFORMACION,
            'origen' => 'notificaciones:prueba-admin',
        ]);
    }

    public function test_no_falla_si_no_hay_administradores_con_suscripcion(): void
    {
        Notification::fake();

        $this->artisan('notificaciones:prueba-admin')->assertSuccessful();

        $this->assertDatabaseHas('logs', [
            'tipo' => RegistroLog::TIPO_INFORMACION,
            'origen' => 'notificaciones:prueba-admin',
        ]);
    }

    public function test_no_esta_registrado_en_el_programador(): void
    {
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);

        $comandosProgramados = collect($schedule->events())->map(fn ($event) => $event->command);

        $this->assertTrue(
            $comandosProgramados->every(fn (?string $comando) => ! str_contains((string) $comando, 'notificaciones:prueba-admin')),
            'El comando de prueba no debe estar registrado en el programador de tareas.'
        );
    }
}
