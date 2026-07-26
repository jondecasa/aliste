<?php

namespace Tests\Feature\Admin;

use App\Models\RegistroLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LogsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);
    }

    public function test_un_redactor_no_puede_acceder(): void
    {
        $redactor = User::factory()->create(['rol' => User::ROL_REDACTOR]);

        $this->actingAs($redactor);

        Volt::test('admin.logs')->assertForbidden();
    }

    public function test_un_administrador_ve_el_listado_de_registros(): void
    {
        $this->actingAs($this->admin());

        RegistroLog::create(['tipo' => RegistroLog::TIPO_ERROR, 'mensaje' => 'Fallo de ejemplo', 'origen' => 'TestOrigen']);
        RegistroLog::create(['tipo' => RegistroLog::TIPO_INFORMACION, 'mensaje' => 'Tarea ok', 'origen' => 'tarea:prueba']);

        Volt::test('admin.logs')
            ->assertSee('Fallo de ejemplo')
            ->assertSee('Tarea ok');
    }

    public function test_el_filtro_por_tipo_funciona(): void
    {
        $this->actingAs($this->admin());

        RegistroLog::create(['tipo' => RegistroLog::TIPO_ERROR, 'mensaje' => 'Solo error']);
        RegistroLog::create(['tipo' => RegistroLog::TIPO_INFORMACION, 'mensaje' => 'Solo info']);

        Volt::test('admin.logs')
            ->set('filtroTipo', RegistroLog::TIPO_ERROR)
            ->assertSee('Solo error')
            ->assertDontSee('Solo info');
    }

    public function test_puede_verse_el_detalle_de_un_registro(): void
    {
        $this->actingAs($this->admin());

        $log = RegistroLog::create([
            'tipo' => RegistroLog::TIPO_EXCEPCION,
            'mensaje' => 'Mensaje de detalle',
            'origen' => 'App\\Ejemplo',
            'contexto' => ['clave' => 'valor'],
        ]);

        Volt::test('admin.logs')
            ->call('ver', $log->id)
            ->assertSee('Mensaje de detalle')
            ->assertSee('valor');
    }

    public function test_eliminar_visibles_borra_solo_los_registros_del_filtro_actual(): void
    {
        $this->actingAs($this->admin());

        RegistroLog::create(['tipo' => RegistroLog::TIPO_ERROR, 'mensaje' => 'Error uno']);
        RegistroLog::create(['tipo' => RegistroLog::TIPO_ERROR, 'mensaje' => 'Error dos']);
        RegistroLog::create(['tipo' => RegistroLog::TIPO_INFORMACION, 'mensaje' => 'Info uno']);

        Volt::test('admin.logs')
            ->set('filtroTipo', RegistroLog::TIPO_ERROR)
            ->call('eliminarVisibles');

        $this->assertDatabaseCount('logs', 1);
        $this->assertDatabaseHas('logs', ['mensaje' => 'Info uno']);
        $this->assertDatabaseMissing('logs', ['mensaje' => 'Error uno']);
        $this->assertDatabaseMissing('logs', ['mensaje' => 'Error dos']);
    }

    public function test_eliminar_visibles_sin_filtro_borra_todos_los_registros(): void
    {
        $this->actingAs($this->admin());

        RegistroLog::create(['tipo' => RegistroLog::TIPO_ERROR, 'mensaje' => 'Error uno']);
        RegistroLog::create(['tipo' => RegistroLog::TIPO_INFORMACION, 'mensaje' => 'Info uno']);

        Volt::test('admin.logs')->call('eliminarVisibles');

        $this->assertDatabaseCount('logs', 0);
    }
}
