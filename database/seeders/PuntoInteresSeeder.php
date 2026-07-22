<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Pueblo;
use App\Models\PuntoInteres;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PuntoInteresSeeder extends Seeder
{
    /**
     * Puntos de interés publicados actualmente. La foto ("foto") es la ruta
     * en storage/app/public/puntos-interes; el archivo debe migrarse aparte,
     * el seeder solo recrea la fila.
     */
    private const PUNTOS = [
        [
            'pueblo' => 'Pobladura',
            'nombre' => 'Iglesia de Nuestra Señora de la Asunción',
            'descripcion' => null,
            'direccion' => null,
            'foto' => 'puntos-interes/wVd1dEJj8IxjM5qxHkxjI5Kch4BG1NidFwZjERBn.jpg',
            'latitud' => '41.8497045',
            'longitud' => '-6.3360771',
            'categorias' => ['Iglesia'],
        ],
    ];

    public function run(): void
    {
        foreach (self::PUNTOS as $datos) {
            $pueblo = Pueblo::where('slug', Str::slug($datos['pueblo']))->first();

            if (! $pueblo) {
                $this->command?->warn("Pueblo no encontrado: {$datos['pueblo']}");

                continue;
            }

            $punto = PuntoInteres::updateOrCreate(
                ['pueblo_id' => $pueblo->id, 'nombre' => $datos['nombre']],
                [
                    'slug' => Str::slug($datos['nombre']),
                    'descripcion' => $datos['descripcion'],
                    'direccion' => $datos['direccion'],
                    'foto' => $datos['foto'],
                    'latitud' => $datos['latitud'],
                    'longitud' => $datos['longitud'],
                ]
            );

            $categoriaIds = Categoria::deGrupo('punto_interes')
                ->whereIn('slug', array_map(Str::slug(...), $datos['categorias']))
                ->pluck('id');

            $punto->categorias()->sync($categoriaIds);
        }
    }
}
