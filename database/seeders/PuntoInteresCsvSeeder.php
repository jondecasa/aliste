<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Pueblo;
use App\Models\PuntoInteres;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Importa el volcado histórico de puntos de interés (Results.csv), procedente
 * de la web anterior de la comarca. Las coordenadas vienen en el formato
 * binario del tipo `geography` de SQL Server (SRID 4326: lon, lat como dos
 * doubles tras una cabecera de 6 bytes), así que hay que decodificarlas.
 *
 * El `IdPueblo` del CSV coincide 1:1 con el `id` de nuestra tabla `pueblos`
 * (verificado contra los 43 pueblos distintos usados en el fichero), así que
 * se usa directamente como pueblo_id.
 *
 * La columna "etiquetas" es texto libre sin estructura fija: a veces es una
 * palabra clave real (fuente, molino, iglesia...), a veces varias separadas
 * por comas, y a veces una frase descriptiva entera. Se intenta reconocer
 * palabras clave que coincidan con las categorías de punto_interes ya
 * existentes; si el texto completo parece más una descripción que una
 * etiqueta (tiene punto final, es largo, o usa lenguaje narrativo), se guarda
 * en el campo `descripcion` en lugar de (o además de) asignar categoría.
 */
class PuntoInteresCsvSeeder extends Seeder
{
    /**
     * Slug de categoría (grupo punto_interes) => palabras/frases que la identifican
     * dentro del texto libre de "etiquetas".
     */
    private const SINONIMOS_CATEGORIA = [
        'fuente' => ['fuente', 'fuentes', 'manantial', 'lavadero', 'pilon', 'pilo', 'caño', 'caños'],
        'molino' => ['molino', 'molinos'],
        'iglesia' => ['iglesia', 'iglesias', 'parroquia'],
        'ermita' => ['ermita', 'santuario'],
        'monumento' => ['monumento', 'monumentos', 'crucero', 'palomar', 'palomares', 'lagar', 'muralla'],
        'puente' => ['puente', 'puentes'],
        'mirador' => ['mirador', 'miradores', 'punto geodesico', 'punto geodésico'],
        'museo' => ['museo'],
        'piscina-natural' => ['piscina', 'piscinas'],
        'polideportivo' => ['polideportivo', 'fronton', 'frontón', 'pista deportiva', 'campo de futbol', 'deporte'],
        'area-recreativa' => ['area recreativa', 'área recreativa', 'merendero', 'merenderos', 'parque juegos', 'area de recreo', 'área de recreo', 'asador'],
        'yacimiento-arqueologico' => ['castro', 'yacimiento', 'restos arqueologicos', 'restos arqueológicos', 'resto arqueologico', 'resto arqueológico'],
        'naturaleza' => ['paraje', 'valle', 'monte', 'montes', 'sierra', 'peña', 'pradera', 'predera', 'arboleda', 'pinar', 'laguna', 'arroyo', 'bosque', 'viñas', 'cañada real', 'paisaje', 'natural'],
    ];

    private const MARCADORES_DESCRIPTIVOS = [
        'antiguamente', 'actualmente', 'estaba', 'habia', 'había', 'peregrinaban',
        'donde', 'ya no existe', 'hubo un', 'estuvo', 'situado en',
    ];

    /** @var array<string, int> slug de categoría => id */
    private array $categoriasPorSlug = [];

    public function run(): void
    {
        $ruta = __DIR__.'/data/puntos_interes.csv';

        if (! file_exists($ruta)) {
            $this->command?->warn('No se encontró database/seeders/data/puntos_interes.csv, se omite la importación.');

            return;
        }

        $this->categoriasPorSlug = Categoria::deGrupo('punto_interes')->pluck('id', 'slug')->all();

        $pueblosPorId = Pueblo::pluck('nombre', 'id');
        $slugsUsados = PuntoInteres::pluck('slug')->flip()->all();

        $conDescripcionPropia = PuntoInteres::whereNotNull('descripcion')
            ->where('descripcion', '!=', '')
            ->get(['pueblo_id', 'nombre'])
            ->map(fn ($p) => $p->pueblo_id.'|'.$p->nombre)
            ->flip()
            ->all();

        $handle = fopen($ruta, 'r');
        fgetcsv($handle); // cabecera

        $importados = 0;
        $omitidos = 0;
        $conCategoria = 0;
        $conDescripcion = 0;

        while (($fila = fgetcsv($handle)) !== false) {
            [$idPoi, $nombre, $localizacion, , $idPueblo, , $etiquetas] = $fila;

            $nombre = trim($nombre, " \t\n\r\0\x0B,");
            $idPueblo = $idPueblo !== '' ? (int) $idPueblo : null;

            if (! $nombre || ! $idPueblo || ! isset($pueblosPorId[$idPueblo])) {
                $this->command?->warn("Fila {$idPoi} omitida: pueblo {$idPueblo} no encontrado o sin nombre.");
                $omitidos++;

                continue;
            }

            $coordenadas = $this->decodificarPunto($localizacion);

            if (! $coordenadas) {
                $this->command?->warn("Fila {$idPoi} omitida: no se pudo leer la localización.");
                $omitidos++;

                continue;
            }

            $slug = $this->slugUnico($nombre, $idPueblo, $slugsUsados);

            [$categoriaSlugs, $descripcion] = $this->clasificarEtiquetas((string) $etiquetas, $pueblosPorId[$idPueblo]);

            $valores = [
                'slug' => $slug,
                'longitud' => $coordenadas['longitud'],
                'latitud' => $coordenadas['latitud'],
            ];

            $clave = $idPueblo.'|'.$nombre;
            if ($descripcion && ! isset($conDescripcionPropia[$clave])) {
                $valores['descripcion'] = $descripcion;
                $conDescripcion++;
            }

            $punto = PuntoInteres::updateOrCreate(
                ['pueblo_id' => $idPueblo, 'nombre' => $nombre],
                $valores
            );

            if ($categoriaSlugs) {
                $ids = array_values(array_filter(array_map(
                    fn ($slug) => $this->categoriasPorSlug[$slug] ?? null,
                    $categoriaSlugs
                )));

                if ($ids) {
                    $punto->categorias()->syncWithoutDetaching($ids);
                    $conCategoria++;
                }
            }

            $slugsUsados[$slug] = true;
            $importados++;
        }

        fclose($handle);

        $this->command?->info(
            "Puntos de interés importados: {$importados} (omitidos: {$omitidos}). "
            ."Con categoría asignada: {$conCategoria}. Con descripción rellenada: {$conDescripcion}."
        );
    }

    /**
     * Decodifica el binario del tipo `geography` de SQL Server para un punto
     * simple: 4 bytes SRID + 1 byte versión + 1 byte flags, seguido de dos
     * doubles little-endian (longitud, latitud).
     *
     * @return array{longitud: float, latitud: float}|null
     */
    private function decodificarPunto(string $hex): ?array
    {
        $hex = ltrim($hex, '0xX');

        if (strlen($hex) < 44) {
            return null;
        }

        $bytes = hex2bin($hex);

        if ($bytes === false || strlen($bytes) < 22) {
            return null;
        }

        $longitud = unpack('e', substr($bytes, 6, 8))[1];
        $latitud = unpack('e', substr($bytes, 14, 8))[1];

        if (! is_finite($longitud) || ! is_finite($latitud)) {
            return null;
        }

        return ['longitud' => round($longitud, 7), 'latitud' => round($latitud, 7)];
    }

    /**
     * @param  array<string, bool>  $slugsUsados
     */
    private function slugUnico(string $nombre, int $puebloId, array &$slugsUsados): string
    {
        $base = Str::slug($nombre);
        $slug = $base;

        if (isset($slugsUsados[$slug])) {
            $slug = $base.'-'.$puebloId;
        }

        $sufijo = 2;
        while (isset($slugsUsados[$slug])) {
            $slug = $base.'-'.$puebloId.'-'.$sufijo;
            $sufijo++;
        }

        return $slug;
    }

    /**
     * @return array{0: list<string>, 1: string|null} [slugs de categoría encontrados, texto para descripcion o null]
     */
    private function clasificarEtiquetas(string $etiquetas, string $nombrePueblo): array
    {
        $etiquetas = trim($etiquetas);

        if ($etiquetas === '' || strtoupper($etiquetas) === 'NULL') {
            return [[], null];
        }

        $normTotal = $this->normalizar($etiquetas);
        if ($normTotal === $this->normalizar($nombrePueblo) || $normTotal === 'aliste') {
            return [[], null];
        }

        $categorias = $this->buscarCategorias($etiquetas);
        $descripcion = $this->esDescriptivo($etiquetas) ? $etiquetas : null;

        return [$categorias, $descripcion];
    }

    /**
     * @return list<string>
     */
    private function buscarCategorias(string $texto): array
    {
        $norm = $this->normalizar($texto);
        $encontradas = [];

        foreach (self::SINONIMOS_CATEGORIA as $slug => $palabras) {
            foreach ($palabras as $palabra) {
                if (preg_match('/(?<![a-z])'.preg_quote($this->normalizar($palabra), '/').'(?![a-z])/', $norm)) {
                    $encontradas[$slug] = true;
                    break;
                }
            }
        }

        return array_keys($encontradas);
    }

    private function esDescriptivo(string $texto): bool
    {
        if (str_contains($texto, '.')) {
            return true;
        }

        if (str_word_count($texto) >= 5) {
            return true;
        }

        if (mb_strlen($texto) > 45) {
            return true;
        }

        $norm = $this->normalizar($texto);

        foreach (self::MARCADORES_DESCRIPTIVOS as $marcador) {
            if (preg_match('/(?<![a-z])'.preg_quote($this->normalizar($marcador), '/').'(?![a-z])/', $norm)) {
                return true;
            }
        }

        return false;
    }

    private function normalizar(string $s): string
    {
        $s = mb_strtolower(trim($s));

        return iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    }
}
