<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Pueblo;
use App\Models\Servicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class BuscarTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_a_prompt_when_there_is_no_query(): void
    {
        Volt::test('public.buscar')
            ->assertSee('Escribe algo para buscar')
            ->assertDontSee('Resultados para');
    }

    public function test_shows_no_results_message_when_nothing_matches(): void
    {
        Volt::test('public.buscar')
            ->set('q', 'esto-no-existe-en-ningun-sitio')
            ->assertSee('No hemos encontrado nada');
    }

    public function test_finds_matches_across_pueblos_servicios_noticias_and_eventos(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $otroPueblo = Pueblo::create(['nombre' => 'Rábano', 'slug' => 'rabano']);

        Servicio::create(['pueblo_id' => $pueblo->id, 'nombre' => 'Panadería Alcañices', 'slug' => 'panaderia-alcanices']);
        Servicio::create(['pueblo_id' => $otroPueblo->id, 'nombre' => 'Ferretería Central', 'slug' => 'ferreteria-central']);

        Noticia::create(['pueblo_id' => $pueblo->id, 'titulo' => 'Fiestas de Alcañices', 'slug' => 'fiestas-alcanices', 'publicado_en' => now()]);
        Noticia::create(['pueblo_id' => $otroPueblo->id, 'titulo' => 'Otra noticia cualquiera', 'slug' => 'otra-noticia', 'publicado_en' => now()]);

        Evento::create(['pueblo_id' => $pueblo->id, 'titulo' => 'Feria de Alcañices', 'slug' => 'feria-alcanices', 'fecha_inicio' => now()]);
        Evento::create(['pueblo_id' => $otroPueblo->id, 'titulo' => 'Otro evento', 'slug' => 'otro-evento', 'fecha_inicio' => now()]);

        Volt::test('public.buscar')
            ->set('q', 'Alcañices')
            ->assertSee('Alcañices') // el propio pueblo
            ->assertSee('Panadería Alcañices')
            ->assertDontSee('Ferretería Central')
            ->assertSee('Fiestas de Alcañices')
            ->assertDontSee('Otra noticia cualquiera')
            ->assertSee('Feria de Alcañices')
            ->assertDontSee('Otro evento');
    }

    public function test_finds_a_service_by_its_category_name(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $categoria = Categoria::create(['nombre' => 'Panadería', 'slug' => 'panaderia', 'grupo' => 'servicio']);

        $servicio = Servicio::create(['pueblo_id' => $pueblo->id, 'nombre' => 'El Horno', 'slug' => 'el-horno']);
        $servicio->categorias()->attach($categoria);

        Volt::test('public.buscar')
            ->set('q', 'Panadería')
            ->assertSee('El Horno');
    }
}
