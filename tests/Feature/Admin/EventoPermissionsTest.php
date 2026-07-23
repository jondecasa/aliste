<?php

namespace Tests\Feature\Admin;

use App\Models\Evento;
use App\Models\Pueblo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;
use Tests\TestCase;

class EventoPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private Pueblo $puebloA;

    private Pueblo $puebloB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->puebloA = Pueblo::create(['nombre' => 'Pueblo A', 'slug' => 'pueblo-a']);
        $this->puebloB = Pueblo::create(['nombre' => 'Pueblo B', 'slug' => 'pueblo-b']);
    }

    private function redactor(Pueblo $pueblo): User
    {
        return User::factory()->create([
            'rol' => User::ROL_REDACTOR,
            'pueblo_id' => $pueblo->id,
        ]);
    }

    private function evento(Pueblo $pueblo, ?User $creador, Carbon $fechaInicio, string $slug): Evento
    {
        return Evento::create([
            'pueblo_id' => $pueblo->id,
            'created_by' => $creador?->id,
            'titulo' => 'Evento '.$slug,
            'slug' => $slug,
            'fecha_inicio' => $fechaInicio,
        ]);
    }

    public function test_redactor_can_edit_their_own_future_event(): void
    {
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloA, $redactor, now()->addDays(3), 'futuro-propio');

        $this->actingAs($redactor);

        Volt::test('admin.eventos')->call('editar', $evento->id)->assertOk();
    }

    public function test_redactor_can_edit_their_own_event_happening_today(): void
    {
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloA, $redactor, now(), 'hoy-propio');

        $this->actingAs($redactor);

        Volt::test('admin.eventos')->call('editar', $evento->id)->assertOk();
    }

    public function test_redactor_cannot_edit_their_own_past_event(): void
    {
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloA, $redactor, now()->subDays(3), 'pasado-propio');

        $this->actingAs($redactor);

        Volt::test('admin.eventos')->call('editar', $evento->id)->assertForbidden();
    }

    public function test_redactor_cannot_edit_a_future_event_created_by_another_redactor(): void
    {
        $otroRedactor = $this->redactor($this->puebloA);
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloA, $otroRedactor, now()->addDays(3), 'futuro-de-otro');

        $this->actingAs($redactor);

        Volt::test('admin.eventos')->call('editar', $evento->id)->assertForbidden();
    }

    public function test_redactor_cannot_access_events_from_another_pueblo(): void
    {
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloB, $redactor, now()->addDays(3), 'de-otro-pueblo');

        $this->actingAs($redactor);

        Volt::test('admin.eventos')->call('editar', $evento->id)->assertForbidden();
    }

    public function test_administrador_can_edit_any_event_regardless_of_date_or_owner(): void
    {
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloA, $redactor, now()->subDays(10), 'pasado-de-redactor');

        $admin = User::factory()->create(['rol' => User::ROL_ADMINISTRADOR]);

        $this->actingAs($admin);

        Volt::test('admin.eventos')->call('editar', $evento->id)->assertOk();
    }

    public function test_new_event_created_by_redactor_records_created_by_automatically(): void
    {
        $redactor = $this->redactor($this->puebloA);

        $this->actingAs($redactor);

        Volt::test('admin.eventos')
            ->call('crear')
            ->set('titulo', 'Evento nuevo')
            ->set('fechaInicio', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('guardar')
            ->assertOk();

        $evento = Evento::where('titulo', 'Evento nuevo')->firstOrFail();

        $this->assertSame($redactor->id, $evento->created_by);
        $this->assertSame($this->puebloA->id, $evento->pueblo_id);
    }

    public function test_redactor_cannot_save_changes_to_their_own_past_event(): void
    {
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloA, $redactor, now()->subDays(3), 'pasado-guardar');

        $this->actingAs($redactor);

        Volt::test('admin.eventos')
            ->set('eventoId', $evento->id)
            ->set('puebloId', $this->puebloA->id)
            ->set('titulo', 'Intento de cambio')
            ->set('fechaInicio', now()->subDays(3)->format('Y-m-d\TH:i'))
            ->call('guardar')
            ->assertForbidden();
    }

    public function test_redactor_cannot_delete_an_event_created_by_another_redactor(): void
    {
        $otroRedactor = $this->redactor($this->puebloA);
        $redactor = $this->redactor($this->puebloA);
        $evento = $this->evento($this->puebloA, $otroRedactor, now()->addDays(3), 'no-mio-eliminar');

        $this->actingAs($redactor);

        Volt::test('admin.eventos')->call('eliminar', $evento->id)->assertForbidden();

        $this->assertNotNull($evento->fresh());
    }
}
