<?php

namespace Tests\Feature;

use App\Models\AudioCancion;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Pueblo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MusicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_muestra_las_canciones(): void
    {
        Cancion::create(['titulo' => 'Ronda de Alcañices', 'slug' => 'ronda-alcanices']);

        Volt::test('public.musica')->assertSee('Ronda de Alcañices');
    }

    public function test_el_listado_filtra_por_categoria(): void
    {
        $categoria = Categoria::create(['nombre' => 'Tonada', 'slug' => 'tonada', 'grupo' => 'cancion']);

        $conCategoria = Cancion::create(['titulo' => 'Con categoría', 'slug' => 'con-categoria']);
        $conCategoria->categorias()->attach($categoria);

        Cancion::create(['titulo' => 'Sin categoría', 'slug' => 'sin-categoria']);

        Volt::test('public.musica')
            ->set('categoriaId', $categoria->id)
            ->assertSee('Con categoría')
            ->assertDontSee('Sin categoría');
    }

    public function test_la_pagina_de_una_cancion_muestra_su_descripcion_html_letra_y_audios(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);

        $cancion = Cancion::create([
            'pueblo_id' => $pueblo->id,
            'titulo' => 'Ronda de Alcañices',
            'slug' => 'ronda-alcanices',
            'artista' => 'Grupo Folk Aliste',
            'descripcion' => '<p>Una <strong>ronda</strong> tradicional.</p>',
            'letra' => "Primer verso\nSegundo verso",
        ]);

        AudioCancion::create([
            'cancion_id' => $cancion->id,
            'archivo' => 'canciones/audios/ronda.mp3',
            'titulo' => 'Grabación de campo',
            'orden' => 1,
        ]);

        $response = $this->get(route('cancion', $cancion));

        $response->assertOk()
            ->assertSee('Ronda de Alcañices')
            ->assertSee('Grupo Folk Aliste')
            ->assertSee('Una <strong>ronda</strong> tradicional.', false)
            ->assertSee('Primer verso')
            ->assertSee('Segundo verso')
            ->assertSee('Grabación de campo');
    }

    public function test_la_pagina_de_una_cancion_se_accede_por_su_slug(): void
    {
        $cancion = Cancion::create(['titulo' => 'Otra canción', 'slug' => 'otra-cancion']);

        $this->get('/musica/otra-cancion')->assertOk();
    }
}
