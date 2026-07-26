<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
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
}
