<?php

namespace Database\Seeders;

use App\Models\Pueblo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PuebloSeeder extends Seeder
{
    /**
     * Los 68 pueblos de la comarca de Aliste tal y como figuran en aliste.info.
     * No se incluyen coordenadas: la web original usa un mapa de imagen estática
     * (no geolocalización real), así que se dejan en null para completarlas
     * más adelante con datos verificados.
     */
    private const PUEBLOS = [
        'Abejera',
        'Alcañices',
        'Alcorcillo',
        'Arcillera',
        'Bercianos',
        'Boya',
        'Brandilanes',
        'Cabañas',
        'Campogrande',
        'Castro de Alcañices',
        'Ceadea',
        'El Poyo',
        'Ferreras de Abajo',
        'Ferreras de Arriba',
        'Figueruela de Abajo',
        'Figueruela de Arriba',
        'Flechas',
        'Flores',
        'Fonfría',
        'Fornillos',
        'Fradellos',
        'Gallegos del Campo',
        'Gallegos del Río',
        'Grisuela',
        'La Torre',
        'Latedo',
        'Lober',
        'Mahíde',
        'Matellanes',
        'Mellanes',
        'Moldones',
        'Moveros',
        'Nuez',
        'Palazuelo',
        'Pobladura',
        'Puercas',
        'Rabanales',
        'Rábano',
        'Ribas',
        'Riofrío',
        'Río Manzanas',
        'Samir',
        'San Blas',
        'San Cristóbal',
        'San Juan',
        'San Mamed',
        'San Martín del Pedroso',
        'San Pedro',
        'San Vicente',
        'San Vitero',
        'Santa Ana',
        'Sarracín',
        'Sejas',
        'Tola',
        'Tolilla',
        'Trabazos',
        'Ufones',
        'Valer',
        'Vega de Nuez',
        'Villarino Cebal',
        'Villarino Manzanas',
        'Villarino tras la Sierra',
        'Viñas',
        'Vivinera',
        'Domez',
        'Bermillo de Alba',
        'Salto de Castro',
        'Pino del Oro',
    ];

    private const CABECERA = 'Alcañices';

    public function run(): void
    {
        foreach (self::PUEBLOS as $nombre) {
            Pueblo::updateOrCreate(
                ['slug' => Str::slug($nombre)],
                [
                    'nombre' => $nombre,
                    'es_cabecera' => $nombre === self::CABECERA,
                ]
            );
        }
    }
}
