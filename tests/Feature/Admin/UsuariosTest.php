<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);
    }

    public function test_un_administrador_puede_bloquear_y_desbloquear_a_otro_usuario(): void
    {
        $this->actingAs($this->admin());

        $usuario = User::factory()->create(['bloqueado' => false]);

        Volt::test('admin.usuarios')->call('cambiarBloqueado', $usuario->id);

        $this->assertTrue($usuario->fresh()->bloqueado);

        Volt::test('admin.usuarios')->call('cambiarBloqueado', $usuario->id);

        $this->assertFalse($usuario->fresh()->bloqueado);
    }

    public function test_un_administrador_no_puede_bloquearse_a_si_mismo(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Volt::test('admin.usuarios')->call('cambiarBloqueado', $admin->id);

        $this->assertFalse($admin->fresh()->bloqueado);
    }
}
