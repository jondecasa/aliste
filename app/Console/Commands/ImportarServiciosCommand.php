<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use App\Models\Pueblo;
use App\Models\Servicio;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportarServiciosCommand extends Command
{
    protected $signature = 'servicios:importar';

    protected $description = 'Importa el listado de servicios publicado en aliste.info';

    private const URL_REMOTO = 'https://aliste.info/es/servicios/_remote.asp';

    public function handle(): int
    {
        $pueblosPorNombre = Pueblo::all()->keyBy(fn (Pueblo $pueblo) => $this->normalizar($pueblo->nombre));
        $pueblosPorNombreSinEspacios = Pueblo::all()
            ->keyBy(fn (Pueblo $pueblo) => str_replace(' ', '', $this->normalizar($pueblo->nombre)));
        $categoriasPorNombre = Categoria::deGrupo('servicio')->get()
            ->keyBy(fn (Categoria $categoria) => $this->normalizar($categoria->nombre));

        $creados = 0;
        $actualizados = 0;
        $pueblosSinMatch = [];
        $categoriasSinMatch = [];
        $totalPaginas = 1;

        for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
            $this->info("Descargando página {$pagina} de {$totalPaginas}...");

            $respuesta = Http::asForm()->post(self::URL_REMOTO.'?'.http_build_query([
                'fx' => 'GTPAG',
                '_oby' => '',
                '_ord' => '',
                'page' => $pagina,
                'var' => '_busquedaServicios',
                'ml' => '',
                'cap' => '',
                'q' => 'printServicios',
                'nrp' => 50,
                'rand' => random_int(1, 999999),
            ]), [
                'form' => 'frmBusquedaServicio',
                '_oby' => '',
                '_ord' => '',
                'modoVis' => 'T',
                'tipoServicio' => '',
                'pueblo' => '',
                'distanciaA' => '',
                'nom' => '',
            ]);

            $cuerpo = $respuesta->body();

            if ($pagina === 1 && preg_match('/_busquedaServicios_npag=(\d+)/', $cuerpo, $coincidencia)) {
                $totalPaginas = (int) $coincidencia[1];
            }

            $filas = $this->extraerFilas($cuerpo);

            foreach ($filas as $fila) {
                $normalizado = $this->normalizar($fila['pueblo']);
                $pueblo = $pueblosPorNombre[$normalizado]
                    ?? $pueblosPorNombreSinEspacios[str_replace(' ', '', $normalizado)]
                    ?? null;

                if (! $pueblo) {
                    $pueblosSinMatch[$fila['pueblo']] = true;

                    continue;
                }

                $servicio = Servicio::firstOrNew([
                    'pueblo_id' => $pueblo->id,
                    'nombre' => $fila['nombre'],
                ]);

                $esNuevo = ! $servicio->exists;

                if ($esNuevo) {
                    $servicio->slug = $this->generarSlugUnico($fila['nombre'], $pueblo->nombre);
                }

                $servicio->direccion = $fila['direccion'];
                $servicio->telefono_1 = $fila['telefono_1'];
                $servicio->telefono_2 = $fila['telefono_2'];
                $servicio->latitud = $fila['latitud'];
                $servicio->longitud = $fila['longitud'];
                $servicio->save();

                $categoriaIds = [];

                foreach ($fila['categorias'] as $nombreCategoria) {
                    $categoria = $categoriasPorNombre[$this->normalizar($nombreCategoria)] ?? null;

                    if ($categoria) {
                        $categoriaIds[] = $categoria->id;
                    } else {
                        $categoriasSinMatch[$nombreCategoria] = true;
                    }
                }

                $servicio->categorias()->sync($categoriaIds);

                $esNuevo ? $creados++ : $actualizados++;
            }
        }

        $this->info("Servicios creados: {$creados}");
        $this->info("Servicios actualizados: {$actualizados}");

        if ($pueblosSinMatch !== []) {
            $this->warn('Pueblos sin correspondencia: '.implode(', ', array_keys($pueblosSinMatch)));
        }

        if ($categoriasSinMatch !== []) {
            $this->warn('Categorías sin correspondencia: '.implode(', ', array_keys($categoriasSinMatch)));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{
     *     nombre: string,
     *     pueblo: string,
     *     direccion: ?string,
     *     telefono_1: ?string,
     *     telefono_2: ?string,
     *     latitud: ?float,
     *     longitud: ?float,
     *     categorias: array<int, string>,
     * }>
     */
    private function extraerFilas(string $html): array
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8">'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodosFila = $xpath->query('//tr[contains(concat(" ", normalize-space(@class), " "), " d0 ") or contains(concat(" ", normalize-space(@class), " "), " d1 ")]');

        $filas = [];

        foreach ($nodosFila as $nodoFila) {
            $celdas = $xpath->query('./td', $nodoFila);

            if ($celdas->length < 7) {
                continue;
            }

            $categorias = [];

            foreach ($xpath->query('.//a[@class="enlazado"]', $celdas->item(1)) as $enlace) {
                $categorias[] = $this->limpiarTexto($enlace->textContent);
            }

            $nombre = $this->limpiarTexto($celdas->item(2)->textContent);

            if ($nombre === '') {
                continue;
            }

            $enlacePueblo = $xpath->query('.//a', $celdas->item(3))->item(0);
            $pueblo = $enlacePueblo ? $this->limpiarTexto($enlacePueblo->textContent) : '';

            if ($pueblo === '') {
                continue;
            }

            $direccion = $this->limpiarTexto($celdas->item(4)->textContent) ?: null;
            $telefono = $this->limpiarTexto($celdas->item(5)->textContent);
            [$telefono1, $telefono2] = $this->dividirTelefonos($telefono);

            [$latitud, $longitud] = $this->extraerCoordenadas($xpath, $celdas->item(6));

            $filas[] = [
                'nombre' => $nombre,
                'pueblo' => $pueblo,
                'direccion' => $direccion,
                'telefono_1' => $telefono1,
                'telefono_2' => $telefono2,
                'latitud' => $latitud,
                'longitud' => $longitud,
                'categorias' => $categorias,
            ];
        }

        return $filas;
    }

    private function extraerCoordenadas(DOMXPath $xpath, \DOMNode $celda): array
    {
        foreach ($xpath->query('.//a', $celda) as $enlace) {
            $href = $enlace->getAttribute('href');

            if (! str_contains($href, 'verPosMapa')) {
                continue;
            }

            parse_str((string) parse_url(html_entity_decode($href), PHP_URL_QUERY), $parametros);

            if (! isset($parametros['lat'], $parametros['long'])) {
                return [null, null];
            }

            // En aliste.info los parámetros "lat" y "long" del enlace del mapa
            // están intercambiados: "lat" contiene la longitud y viceversa.
            return [(float) $parametros['long'], (float) $parametros['lat']];
        }

        return [null, null];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function dividirTelefonos(string $telefono): array
    {
        if ($telefono === '') {
            return [null, null];
        }

        if (str_contains($telefono, '-')) {
            $partes = array_map('trim', explode('-', $telefono, 2));

            return [$partes[0] ?: null, $partes[1] ?? null];
        }

        if (mb_strlen($telefono) <= 30) {
            return [$telefono, null];
        }

        $partes = preg_split('/\s+/', $telefono) ?: [];

        return [$partes[0] ?? null, $partes[1] ?? null];
    }

    private function generarSlugUnico(string $nombre, string $nombrePueblo): string
    {
        $base = Str::slug($nombre.'-'.$nombrePueblo);
        $slug = $base;
        $sufijo = 2;

        while (Servicio::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$sufijo++;
        }

        return $slug;
    }

    private function limpiarTexto(string $texto): string
    {
        return trim(str_replace("\u{00A0}", ' ', $texto));
    }

    private function normalizar(string $texto): string
    {
        return Str::of($texto)->ascii()->lower()->squish()->trim()->value();
    }
}
