<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pueblo;
use App\Models\Servicio;
use Database\Seeders\ServicioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ServicioSeederNoVistosTest extends TestCase
{
    use RefreshDatabase;

    private const URL_REMOTO = 'https://aliste.info/es/servicios/_remote.asp';

    private function filaHtml(string $nombre, string $pueblo, string $claseFila): string
    {
        return <<<HTML
        <tr class="{$claseFila}">
            <td>0</td>
            <td><a class="enlazado">Bar</a></td>
            <td>{$nombre}</td>
            <td><a>{$pueblo}</a></td>
            <td>Calle Mayor 1</td>
            <td>123456789</td>
            <td></td>
        </tr>
        HTML;
    }

    public function test_los_servicios_que_desaparecen_de_la_fuente_quedan_marcados_como_no_vistos(): void
    {
        Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        Categoria::create(['nombre' => 'Bar', 'slug' => 'bar', 'grupo' => 'servicio']);

        // Primera pasada: existen "Bar A" y "Bar B". Segunda pasada, más
        // tarde: "Bar B" ya no está en la fuente.
        Http::fakeSequence(self::URL_REMOTO.'*')
            ->push('<table>'.$this->filaHtml('Bar A', 'Alcañices', 'd0').$this->filaHtml('Bar B', 'Alcañices', 'd1').'</table>')
            ->push('<table>'.$this->filaHtml('Bar A', 'Alcañices', 'd0').'</table>');

        (new ServicioSeeder())->run();

        $barA = Servicio::where('nombre', 'Bar A')->firstOrFail();
        $barB = Servicio::where('nombre', 'Bar B')->firstOrFail();

        $this->assertNotNull($barA->visto_en_importacion_en);
        $this->assertNotNull($barB->visto_en_importacion_en);

        $this->travel(1)->hour();

        (new ServicioSeeder())->run();

        $noVistos = Servicio::noVistosEnUltimaImportacion()->pluck('nombre');

        $this->assertTrue($noVistos->contains('Bar B'));
        $this->assertFalse($noVistos->contains('Bar A'));
    }

    public function test_los_servicios_creados_a_mano_nunca_aparecen_como_no_vistos(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        Categoria::create(['nombre' => 'Bar', 'slug' => 'bar', 'grupo' => 'servicio']);

        Http::fake([
            self::URL_REMOTO.'*' => Http::response(
                '<table>'.$this->filaHtml('Bar A', 'Alcañices', 'd0').'</table>'
            ),
        ]);

        (new ServicioSeeder())->run();

        $manual = Servicio::create([
            'pueblo_id' => $pueblo->id,
            'nombre' => 'Creado a mano',
            'slug' => 'creado-a-mano',
            'prioridad' => 5,
        ]);

        $noVistos = Servicio::noVistosEnUltimaImportacion()->pluck('nombre');

        $this->assertFalse($noVistos->contains('Creado a mano'));
        $this->assertNull($manual->fresh()->visto_en_importacion_en);
    }
}
