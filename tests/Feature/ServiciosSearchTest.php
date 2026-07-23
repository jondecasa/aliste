<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pueblo;
use App\Models\Servicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ServiciosSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_find_a_service_by_its_own_name(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);

        Servicio::create(['pueblo_id' => $pueblo->id, 'nombre' => 'Panadería El Horno', 'slug' => 'panaderia-el-horno']);
        Servicio::create(['pueblo_id' => $pueblo->id, 'nombre' => 'Ferretería Central', 'slug' => 'ferreteria-central']);

        Volt::test('public.servicios')
            ->set('buscar', 'Horno')
            ->assertSee('Panadería El Horno')
            ->assertDontSee('Ferretería Central');
    }

    public function test_can_find_a_service_by_its_pueblo_name(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $otroPueblo = Pueblo::create(['nombre' => 'Rábano', 'slug' => 'rabano']);

        Servicio::create(['pueblo_id' => $pueblo->id, 'nombre' => 'Panadería El Horno', 'slug' => 'panaderia-el-horno']);
        Servicio::create(['pueblo_id' => $otroPueblo->id, 'nombre' => 'Ferretería Central', 'slug' => 'ferreteria-central']);

        Volt::test('public.servicios')
            ->set('buscar', 'Alcañices')
            ->assertSee('Panadería El Horno')
            ->assertDontSee('Ferretería Central');
    }

    public function test_can_find_a_service_by_its_category_name(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);

        $categoriaPanaderia = Categoria::create(['nombre' => 'Panadería', 'slug' => 'panaderia', 'grupo' => 'servicio']);
        $categoriaFerreteria = Categoria::create(['nombre' => 'Ferretería', 'slug' => 'ferreteria', 'grupo' => 'servicio']);

        $panaderia = Servicio::create(['pueblo_id' => $pueblo->id, 'nombre' => 'El Horno', 'slug' => 'el-horno']);
        $panaderia->categorias()->attach($categoriaPanaderia);

        $ferreteria = Servicio::create(['pueblo_id' => $pueblo->id, 'nombre' => 'Central', 'slug' => 'central']);
        $ferreteria->categorias()->attach($categoriaFerreteria);

        Volt::test('public.servicios')
            ->set('buscar', 'Panadería')
            ->assertSee('El Horno')
            ->assertDontSee('Central');
    }
}
