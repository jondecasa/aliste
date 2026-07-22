<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use App\Models\Pueblo;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScrapearNoticiasCommand extends Command
{
    protected $signature = 'noticias:scrapear';

    protected $description = 'Importa noticias recientes de la comarca desde ZA49 (máximo 2, publicadas en las últimas 6 horas)';

    private const URL_CATEGORIA = 'https://www.za49.es/aliste/';

    private const HORAS_RECIENCIA = 6;

    private const MAXIMO_POR_EJECUCION = 2;

    public function handle(): int
    {
        $enlaces = $this->extraerEnlaces();

        if ($enlaces === []) {
            $this->info('No se encontraron artículos en la categoría Aliste de ZA49.');

            return self::SUCCESS;
        }

        $limite = now()->subHours(self::HORAS_RECIENCIA);
        $candidatos = [];

        foreach ($enlaces as $url) {
            if (Noticia::where('fuente_url', $url)->exists()) {
                continue;
            }

            $datos = $this->leerArticulo($url);

            if (! $datos || $datos['publicadoEn']->lt($limite)) {
                continue;
            }

            $candidatos[] = $datos;
        }

        if ($candidatos === []) {
            $this->info('No hay artículos nuevos publicados en las últimas '.self::HORAS_RECIENCIA.' horas.');

            return self::SUCCESS;
        }

        usort($candidatos, fn (array $a, array $b) => $b['publicadoEn'] <=> $a['publicadoEn']);
        $candidatos = array_slice($candidatos, 0, self::MAXIMO_POR_EJECUCION);

        $pueblos = Pueblo::all();

        foreach ($candidatos as $datos) {
            $this->importar($datos, $pueblos);
        }

        $this->info(count($candidatos).' noticia(s) importada(s).');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function extraerEnlaces(): array
    {
        try {
            $respuesta = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get(self::URL_CATEGORIA);
        } catch (\Throwable) {
            $this->warn('No se pudo descargar la categoría de Aliste en ZA49.');

            return [];
        }

        if (! $respuesta->successful()) {
            $this->warn("ZA49 devolvió un error ({$respuesta->status()}).");

            return [];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8">'.$respuesta->body());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $enlaces = [];

        foreach ($xpath->query('//a[contains(@href, "/aliste/")]') as $nodo) {
            $href = $nodo->getAttribute('href');

            if (! preg_match('/\/aliste\/[a-z0-9-]+_\d+_\d+\.html$/', $href)) {
                continue;
            }

            $url = str_starts_with($href, 'http') ? $href : 'https://www.za49.es'.$href;
            $enlaces[$url] = true;
        }

        return array_keys($enlaces);
    }

    /**
     * @return array{titulo: string, extracto: ?string, imagenUrl: ?string, url: string, publicadoEn: Carbon}|null
     */
    private function leerArticulo(string $url): ?array
    {
        try {
            $respuesta = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $respuesta->successful()) {
            return null;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8">'.$respuesta->body());
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $fecha = $xpath->query('//meta[@itemprop="datePublished"]/@content')->item(0)?->nodeValue;

        if (! $fecha) {
            return null;
        }

        $titulo = trim($xpath->query('//title')->item(0)?->textContent ?? '');

        if ($titulo === '') {
            return null;
        }

        $extracto = $xpath->query('//meta[@property="og:description"]/@content')->item(0)?->nodeValue;
        $imagenUrl = $xpath->query('//meta[@property="og:image"]/@content')->item(0)?->nodeValue;

        return [
            'titulo' => $titulo,
            'extracto' => $extracto !== null ? trim($extracto) : null,
            'imagenUrl' => $imagenUrl,
            'url' => $url,
            'publicadoEn' => Carbon::parse($fecha),
        ];
    }

    /**
     * @param  array{titulo: string, extracto: ?string, imagenUrl: ?string, url: string, publicadoEn: Carbon}  $datos
     * @param  Collection<int, Pueblo>  $pueblos
     */
    private function importar(array $datos, Collection $pueblos): void
    {
        $slug = $this->generarSlugUnico($datos['titulo']);

        $noticia = Noticia::create([
            'pueblo_id' => $this->detectarPueblo($datos['titulo'].' '.$datos['extracto'], $pueblos),
            'titulo' => $datos['titulo'],
            'slug' => $slug,
            'extracto' => $datos['extracto'],
            'fuente_nombre' => 'ZA49',
            'fuente_url' => $datos['url'],
            'url_externa' => $datos['url'],
            'publicado_en' => $datos['publicadoEn'],
        ]);

        if ($datos['imagenUrl'] && $ruta = $this->descargarImagen($datos['imagenUrl'], $slug)) {
            $noticia->update(['imagen_portada' => Storage::disk('public')->url($ruta)]);
        }

        $this->info("Importada: {$datos['titulo']}");
    }

    private function generarSlugUnico(string $titulo): string
    {
        $base = Str::slug($titulo);
        $slug = $base;
        $sufijo = 2;

        while (Noticia::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$sufijo++;
        }

        return $slug;
    }

    /**
     * @param  Collection<int, Pueblo>  $pueblos
     */
    private function detectarPueblo(string $texto, Collection $pueblos): ?int
    {
        $normalizado = Str::of($texto)->ascii()->lower()->value();

        foreach ($pueblos as $pueblo) {
            $nombreNormalizado = Str::of($pueblo->nombre)->ascii()->lower()->value();

            if (str_contains($normalizado, $nombreNormalizado)) {
                return $pueblo->id;
            }
        }

        return null;
    }

    private function descargarImagen(string $url, string $slug): ?string
    {
        try {
            $respuesta = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
        } catch (\Throwable) {
            $this->warn("No se pudo descargar la imagen: {$url}");

            return null;
        }

        if (! $respuesta->successful()) {
            $this->warn("No se pudo descargar la imagen ({$respuesta->status()}): {$url}");

            return null;
        }

        $cuerpo = $respuesta->body();
        $extension = match (true) {
            str_starts_with($cuerpo, "\x89PNG") => 'png',
            str_starts_with($cuerpo, "\xFF\xD8\xFF") => 'jpg',
            default => null,
        };

        if (! $extension) {
            $this->warn("La URL no devolvió una imagen reconocible: {$url}");

            return null;
        }

        $ruta = "noticias/{$slug}.{$extension}";
        Storage::disk('public')->put($ruta, $cuerpo);

        return $ruta;
    }
}
