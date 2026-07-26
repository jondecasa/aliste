<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_estado_invalido_redirige_al_login_con_un_aviso_en_vez_de_romper(): void
    {
        $proveedor = Mockery::mock(Provider::class);
        $proveedor->shouldReceive('user')->andThrow(new InvalidStateException());

        Socialite::shouldReceive('driver')->with('google')->andReturn($proveedor);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('logs', [
            'origen' => InvalidStateException::class,
        ]);
    }

    public function test_un_usuario_bloqueado_no_puede_entrar_con_google(): void
    {
        $user = User::factory()->create(['google_id' => '12345', 'bloqueado' => true]);

        $googleUser = new SocialiteUser();
        $googleUser->id = '12345';
        $googleUser->email = $user->email;
        $googleUser->name = $user->name;

        $proveedor = Mockery::mock(Provider::class);
        $proveedor->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($proveedor);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
