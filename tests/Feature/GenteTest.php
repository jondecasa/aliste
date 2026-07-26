<?php

namespace Tests\Feature;

use App\Models\Pueblo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class GenteTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_usuarios_bloqueados_no_aparecen_en_el_listado_de_gente_del_pueblo(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);

        $visible = User::factory()->create(['pueblo_id' => $pueblo->id, 'name' => 'Persona Visible']);
        $bloqueado = User::factory()->create(['pueblo_id' => $pueblo->id, 'name' => 'Persona Bloqueada', 'bloqueado' => true]);

        Volt::test('public.gente', ['pueblo' => $pueblo])
            ->assertSee($visible->name)
            ->assertDontSee($bloqueado->name);
    }
}
