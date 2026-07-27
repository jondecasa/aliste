<?php

namespace Tests\Feature;

use App\Models\Pueblo;
use App\Models\User;
use App\Notifications\NuevoUsuarioRegistrado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class NotificarAdminNuevoRegistroTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConSuscripcion(): User
    {
        $admin = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);
        $admin->pushSubscriptions()->create([
            'endpoint' => 'https://push.example.com/'.$admin->id,
            'public_key' => 'clave-publica',
            'auth_token' => 'token-auth',
            'content_encoding' => 'aesgcm',
        ]);

        return $admin;
    }

    public function test_notifica_a_los_administradores_con_suscripcion_al_registrarse_un_usuario(): void
    {
        Notification::fake();

        $admin = $this->crearAdminConSuscripcion();
        $adminSinSuscripcion = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);

        Volt::test('pages.auth.register')
            ->set('name', 'Ana Ejemplo')
            ->set('puebloId', $pueblo->id)
            ->set('email', 'ana@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('aceptaTerminos', true)
            ->call('register');

        Notification::assertSentTo($admin, NuevoUsuarioRegistrado::class, function ($notification) {
            $mensaje = $notification->toWebPush($notification, $notification)->toArray();

            return str_contains($mensaje['body'], 'Ana Ejemplo de Alcañices se ha registrado');
        });

        Notification::assertNotSentTo($adminSinSuscripcion, NuevoUsuarioRegistrado::class);
    }

    public function test_el_mensaje_no_menciona_pueblo_si_el_usuario_no_eligio_ninguno(): void
    {
        Notification::fake();

        $admin = $this->crearAdminConSuscripcion();

        Volt::test('pages.auth.register')
            ->set('name', 'Luis Sin Pueblo')
            ->set('email', 'luis@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('aceptaTerminos', true)
            ->call('register');

        Notification::assertSentTo($admin, NuevoUsuarioRegistrado::class, function ($notification) {
            $mensaje = $notification->toWebPush($notification, $notification)->toArray();

            return $mensaje['body'] === 'Luis Sin Pueblo se ha registrado';
        });
    }

    public function test_no_falla_si_no_hay_administradores_con_suscripcion(): void
    {
        Notification::fake();

        Volt::test('pages.auth.register')
            ->set('name', 'Sin Admins')
            ->set('email', 'sinadmins@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('aceptaTerminos', true)
            ->call('register')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'sinadmins@example.com']);
    }
}
