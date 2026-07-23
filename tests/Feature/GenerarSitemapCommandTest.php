<?php

namespace Tests\Feature;

use App\Models\Noticia;
use App\Models\Pueblo;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GenerarSitemapCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::delete(public_path('sitemap.xml'));

        parent::tearDown();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function ubicacionesDelSitemap(string $ruta): \Illuminate\Support\Collection
    {
        $documento = new DOMDocument();
        $documento->load($ruta);

        $xpath = new DOMXPath($documento);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $locs = [];
        foreach ($xpath->query('//s:url/s:loc') as $nodo) {
            $locs[] = $nodo->textContent;
        }

        return collect($locs);
    }

    public function test_generates_a_valid_sitemap_with_static_pueblos_and_noticias_urls(): void
    {
        $pueblo = Pueblo::create(['nombre' => 'Alcañices', 'slug' => 'alcanices']);
        $noticia = Noticia::create(['titulo' => 'Una noticia', 'slug' => 'una-noticia-de-prueba', 'publicado_en' => now()]);

        $this->artisan('sitemap:generar')->assertSuccessful();

        $ruta = public_path('sitemap.xml');
        $this->assertFileExists($ruta);

        $locs = $this->ubicacionesDelSitemap($ruta);

        $this->assertTrue($locs->contains(route('inicio')));
        $this->assertTrue($locs->contains(route('pueblo', $pueblo)));
        $this->assertTrue($locs->contains(route('pueblo.calendario', $pueblo)));
        $this->assertTrue($locs->contains(route('noticia', $noticia)));
    }
}
