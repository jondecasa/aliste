<?php

namespace Tests\Unit;

use App\Models\Evento;
use App\Models\Pueblo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoDiaLogicoTest extends TestCase
{
    use RefreshDatabase;

    private function evento(string $fechaInicio): Evento
    {
        $pueblo = Pueblo::first() ?? Pueblo::create(['nombre' => 'Pueblo de prueba', 'slug' => 'pueblo-prueba']);

        return Evento::create([
            'pueblo_id' => $pueblo->id,
            'titulo' => 'Evento de prueba',
            'slug' => 'evento-prueba-'.uniqid(),
            'fecha_inicio' => $fechaInicio,
        ]);
    }

    public function test_un_evento_de_madrugada_se_agrupa_con_el_dia_anterior(): void
    {
        $evento = $this->evento('2026-07-24 00:30:00');

        $this->assertSame('2026-07-23', $evento->dia_logico);
        $this->assertSame('2026-07-23', $evento->inicio_calendario->toDateString());
        $this->assertSame('00:30', $evento->inicio_calendario->format('H:i'));
    }

    public function test_un_evento_justo_antes_del_corte_tambien_se_agrupa_con_el_dia_anterior(): void
    {
        $evento = $this->evento('2026-07-24 04:59:00');

        $this->assertSame('2026-07-23', $evento->dia_logico);
    }

    public function test_un_evento_a_las_5_en_punto_ya_cuenta_como_el_nuevo_dia(): void
    {
        $evento = $this->evento('2026-07-24 05:00:00');

        $this->assertSame('2026-07-24', $evento->dia_logico);
    }

    public function test_un_evento_de_tarde_no_se_desplaza(): void
    {
        $evento = $this->evento('2026-07-23 20:00:00');

        $this->assertSame('2026-07-23', $evento->dia_logico);
        $this->assertSame('20:00', $evento->inicio_calendario->format('H:i'));
    }

    public function test_el_orden_logico_deja_los_eventos_de_madrugada_al_final(): void
    {
        $tarde = $this->evento('2026-07-23 20:00:00');
        $madrugada = $this->evento('2026-07-24 00:30:00');

        $this->assertTrue($madrugada->orden_logico > $tarde->orden_logico);
    }
}
