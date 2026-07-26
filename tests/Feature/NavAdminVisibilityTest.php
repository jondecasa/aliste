<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavAdminVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_administrador_ve_el_enlace_administracion_en_el_navbar_publico(): void
    {
        $admin = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertSee('Administración');
    }

    public function test_un_redactor_ve_el_enlace_administracion_en_el_navbar_publico(): void
    {
        $redactor = User::factory()->create(['rol' => User::ROL_REDACTOR]);

        $response = $this->actingAs($redactor)->get('/');

        $response->assertOk();
        $response->assertSee('Administración');
    }

    public function test_un_invitado_no_ve_el_enlace_administracion(): void
    {
        $invitado = User::factory()->create(['rol' => User::ROL_INVITADO]);

        $response = $this->actingAs($invitado)->get('/');

        $response->assertOk();
        $response->assertDontSee('Administración');
    }
}
