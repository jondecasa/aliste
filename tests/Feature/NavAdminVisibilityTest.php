<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavAdminVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_navbar_publico_nunca_muestra_administracion_aunque_el_usuario_tenga_permiso(): void
    {
        $admin = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertDontSee('Administración');
    }

    public function test_un_administrador_ve_administracion_en_el_navbar_del_backoffice(): void
    {
        $admin = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $response = $this->actingAs($admin)->get(route('profile'));

        $response->assertOk();
        $response->assertSee('Administración');
    }

    public function test_un_redactor_ve_administracion_en_el_navbar_del_backoffice(): void
    {
        $redactor = User::factory()->create(['rol' => User::ROL_REDACTOR]);

        $response = $this->actingAs($redactor)->get(route('profile'));

        $response->assertOk();
        $response->assertSee('Administración');
    }

    public function test_un_invitado_no_ve_administracion_en_el_navbar_del_backoffice(): void
    {
        $invitado = User::factory()->create(['rol' => User::ROL_INVITADO]);

        $response = $this->actingAs($invitado)->get(route('profile'));

        $response->assertOk();
        $response->assertDontSee('Administración');
    }
}
