<?php

namespace Tests\Feature;

use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScrapearNoticiasCommandTest extends TestCase
{
    use RefreshDatabase;

    private const URL_CATEGORIA = 'https://www.za49.es/aliste/';

    private function paginaCategoria(array $urls): string
    {
        $enlaces = collect($urls)->map(fn ($url) => "<a href=\"{$url}\">Ver más</a>")->implode('');

        return "<html><body>{$enlaces}</body></html>";
    }

    private function paginaArticulo(string $titulo, string $extracto, string $publicadoEn): string
    {
        return <<<HTML
        <html><head>
            <title>{$titulo}</title>
            <meta itemprop="datePublished" content="{$publicadoEn}">
            <meta property="og:description" content="{$extracto}">
            <meta property="og:image" content="https://www.za49.es/img/foto.jpg">
        </head><body></body></html>
        HTML;
    }

    public function test_importa_articulos_recientes_no_vistos_de_za49(): void
    {
        $url = 'https://www.za49.es/aliste/una-noticia-cualquiera_1_100001.html';

        Http::fake([
            self::URL_CATEGORIA => Http::response($this->paginaCategoria([$url])),
            $url => Http::response($this->paginaArticulo('Una noticia cualquiera', 'Extracto de prueba', now()->subHour()->toIso8601String())),
            '*' => Http::response('', 200),
        ]);

        $this->artisan('noticias:scrapear')->assertSuccessful();

        $this->assertDatabaseHas('noticias', [
            'titulo' => 'Una noticia cualquiera',
            'fuente_nombre' => 'ZA49',
            'fuente_url' => $url,
        ]);
    }

    public function test_no_reimporta_una_url_ya_registrada(): void
    {
        $url = 'https://www.za49.es/aliste/ya-importada_1_100002.html';

        Noticia::create([
            'titulo' => 'Ya importada',
            'slug' => 'ya-importada',
            'fuente_nombre' => 'ZA49',
            'fuente_url' => $url,
            'publicado_en' => now()->subHour(),
        ]);

        Http::fake([
            self::URL_CATEGORIA => Http::response($this->paginaCategoria([$url])),
            '*' => Http::response('', 200),
        ]);

        $this->artisan('noticias:scrapear')->assertSuccessful();

        $this->assertDatabaseCount('noticias', 1);
    }

    public function test_descarta_un_titulo_muy_similar_a_una_noticia_reciente_aunque_la_url_sea_distinta(): void
    {
        Noticia::create([
            'titulo' => 'Alcañices celebra sus fiestas patronales con gran éxito de público',
            'slug' => 'alcanices-fiestas-patronales',
            'fuente_nombre' => 'ZA49',
            'fuente_url' => 'https://www.za49.es/aliste/otra-url-anterior_1_090000.html',
            'publicado_en' => now()->subHours(2),
        ]);

        $url = 'https://www.za49.es/aliste/alcanices-celebra-fiestas_1_100003.html';

        Http::fake([
            self::URL_CATEGORIA => Http::response($this->paginaCategoria([$url])),
            $url => Http::response($this->paginaArticulo(
                'Alcañices celebra sus fiestas patronales con gran exito de publico',
                'Otra redacción del mismo suceso',
                now()->subMinutes(30)->toIso8601String()
            )),
            '*' => Http::response('', 200),
        ]);

        $this->artisan('noticias:scrapear')->assertSuccessful();

        $this->assertDatabaseCount('noticias', 1);
        $this->assertDatabaseMissing('noticias', ['fuente_url' => $url]);
    }

    public function test_importa_un_titulo_distinto_aunque_haya_noticias_recientes(): void
    {
        Noticia::create([
            'titulo' => 'Rábano estrena nuevo consultorio médico',
            'slug' => 'rabano-consultorio',
            'fuente_nombre' => 'ZA49',
            'fuente_url' => 'https://www.za49.es/aliste/otra-url_1_090001.html',
            'publicado_en' => now()->subHours(2),
        ]);

        $url = 'https://www.za49.es/aliste/otro-suceso-totalmente-distinto_1_100004.html';

        Http::fake([
            self::URL_CATEGORIA => Http::response($this->paginaCategoria([$url])),
            $url => Http::response($this->paginaArticulo(
                'San Vitero organiza un mercado artesanal este fin de semana',
                'Extracto distinto',
                now()->subMinutes(30)->toIso8601String()
            )),
            '*' => Http::response('', 200),
        ]);

        $this->artisan('noticias:scrapear')->assertSuccessful();

        $this->assertDatabaseCount('noticias', 2);
        $this->assertDatabaseHas('noticias', ['fuente_url' => $url]);
    }
}
