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

    public function test_un_evento_de_madrugada_no_cambia_de_dia(): void
    {
        $evento = $this->evento('2026-07-13 01:00:00');

        $this->assertSame('2026-07-13', $evento->fecha_inicio->toDateString());
        $this->assertSame('01:00', $evento->fecha_inicio->format('H:i'));
    }

    public function test_el_orden_logico_de_un_evento_de_madrugada_es_mayor_que_uno_de_tarde_del_mismo_dia(): void
    {
        $tarde = $this->evento('2026-07-13 20:00:00');
        $madrugada = $this->evento('2026-07-13 01:00:00');

        $this->assertTrue($madrugada->orden_logico > $tarde->orden_logico);
    }

    public function test_el_orden_logico_justo_antes_del_corte_sigue_contando_como_madrugada(): void
    {
        $evento = $this->evento('2026-07-13 04:59:00');

        $this->assertSame((4 * 60 + 59) + (24 * 60), $evento->orden_logico);
    }

    public function test_el_orden_logico_a_las_5_en_punto_ya_no_se_desplaza(): void
    {
        $evento = $this->evento('2026-07-13 05:00:00');

        $this->assertSame(5 * 60, $evento->orden_logico);
    }

    public function test_el_orden_logico_de_un_evento_de_tarde_es_su_hora_normal(): void
    {
        $evento = $this->evento('2026-07-13 20:00:00');

        $this->assertSame(20 * 60, $evento->orden_logico);
    }
}
