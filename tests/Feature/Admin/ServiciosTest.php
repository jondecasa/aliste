<?php

namespace Tests\Feature\Admin;

use App\Models\Pueblo;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ServiciosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);
    }

    private function crearServicio(Pueblo $pueblo, string $nombre): Servicio
    {
        return Servicio::create([
            'pueblo_id' => $pueblo->id,
            'nombre' => $nombre,
            'slug' => \Illuminate\Support\Str::slug($nombre),
            'prioridad' => 5,
        ]);
    }

    public function test_el_enlace_de_google_maps_usa_las_coordenadas_cuando_existen(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $servicio = $this->crearServicio($pueblo, 'Bar El Cruce');
        $servicio->update(['latitud' => 41.7091234, 'longitud' => -6.4622345]);

        $enlace = $servicio->fresh()->enlace_maps;

        $this->assertSame('https://www.google.com/maps/search/?api=1&query=41.7091234,-6.4622345', $enlace);
    }

    public function test_el_enlace_de_google_maps_busca_por_nombre_y_pueblo_sin_coordenadas(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $servicio = $this->crearServicio($pueblo, 'Bar El Cruce');

        $enlace = $servicio->enlace_maps;

        $this->assertStringStartsWith('https://www.google.com/maps/search/?api=1&query=', $enlace);
        $this->assertStringContainsString(urlencode('Bar El Cruce, Alcañices, Zamora'), $enlace);
    }

    public function test_un_administrador_puede_marcar_un_servicio_como_revisado(): void
    {
        $this->actingAs($this->admin());

        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $servicio = $this->crearServicio($pueblo, 'Bar El Cruce');

        Volt::test('admin.servicios')->call('marcarRevisado', $servicio->id);

        $this->assertNotNull($servicio->fresh()->revisado_en);
    }

    public function test_el_filtro_ocultar_revisados_excluye_los_ya_marcados(): void
    {
        $this->actingAs($this->admin());

        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $revisado = $this->crearServicio($pueblo, 'Ya revisado');
        $revisado->update(['revisado_en' => now()]);
        $pendiente = $this->crearServicio($pueblo, 'Pendiente de revisar');

        Volt::test('admin.servicios')
            ->set('ocultarRevisados', true)
            ->assertSee('Pendiente de revisar')
            ->assertDontSee('Ya revisado');
    }

    public function test_el_filtro_de_pueblo_solo_muestra_servicios_de_ese_pueblo(): void
    {
        $this->actingAs($this->admin());

        $alcanices = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $rabanales = Pueblo::create(['nombre' => 'Rabanales', 'slug' => 'rabanales']);
        $this->crearServicio($alcanices, 'Bar de Alcañices');
        $this->crearServicio($rabanales, 'Bar de Rabanales');

        Volt::test('admin.servicios')
            ->set('puebloFiltroId', $alcanices->id)
            ->assertSee('Bar de Alcañices')
            ->assertDontSee('Bar de Rabanales');
    }
}
