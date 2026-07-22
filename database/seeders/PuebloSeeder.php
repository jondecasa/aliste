<?php

namespace Database\Seeders;

use App\Models\Pueblo;
use Illuminate\Database\Seeder;

class PuebloSeeder extends Seeder
{
    /**
     * Los 66 pueblos de la comarca de Aliste, con el contenido (descripción,
     * portada, población, altitud, coordenadas) tal y como está publicado
     * actualmente en el sitio. El snapshot vive en data/pueblos.json.
     */
    public function run(): void
    {
        $pueblos = json_decode(file_get_contents(__DIR__.'/data/pueblos.json'), true);

        foreach ($pueblos as $datos) {
            Pueblo::updateOrCreate(
                ['slug' => $datos['slug']],
                [
                    'nombre' => $datos['nombre'],
                    'latitud' => $datos['latitud'],
                    'longitud' => $datos['longitud'],
                    'descripcion' => $datos['descripcion'],
                    'contenido_html' => $datos['contenido_html'],
                    'portada' => $datos['portada'],
                    'poblacion' => $datos['poblacion'],
                    'altitud' => $datos['altitud'],
                    'es_cabecera' => $datos['es_cabecera'],
                ]
            );
        }
    }
}
