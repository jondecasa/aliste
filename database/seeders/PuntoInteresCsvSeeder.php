<?php

namespace Database\Seeders;

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
 */
class PuntoInteresCsvSeeder extends Seeder
{
    public function run(): void
    {
        $ruta = __DIR__.'/data/puntos_interes.csv';

        if (! file_exists($ruta)) {
            $this->command?->warn('No se encontró database/seeders/data/puntos_interes.csv, se omite la importación.');

            return;
        }

        $pueblosExistentes = Pueblo::pluck('id')->flip();
        $slugsUsados = PuntoInteres::pluck('slug')->flip()->all();

        $handle = fopen($ruta, 'r');
        fgetcsv($handle); // cabecera

        $importados = 0;
        $omitidos = 0;

        while (($fila = fgetcsv($handle)) !== false) {
            [$idPoi, $nombre, $localizacion, , $idPueblo] = $fila;

            $nombre = trim($nombre, " \t\n\r\0\x0B,");
            $idPueblo = $idPueblo !== '' ? (int) $idPueblo : null;

            if (! $nombre || ! $idPueblo || ! isset($pueblosExistentes[$idPueblo])) {
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

            $slug = $this->slugUnico($nombre, (int) $idPueblo, $slugsUsados);

            PuntoInteres::updateOrCreate(
                ['pueblo_id' => $idPueblo, 'nombre' => $nombre],
                [
                    'slug' => $slug,
                    'longitud' => $coordenadas['longitud'],
                    'latitud' => $coordenadas['latitud'],
                ]
            );

            $slugsUsados[$slug] = true;
            $importados++;
        }

        fclose($handle);

        $this->command?->info("Puntos de interés importados: {$importados} (omitidos: {$omitidos}).");
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
}
