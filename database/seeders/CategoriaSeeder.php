<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaSeeder extends Seeder
{
    /**
     * Etiquetas de noticias tal y como aparecen en aliste.info.
     */
    private const NOTICIAS = [
        'Alistanos por el mundo',
        'Arquitectura',
        'Asociaciones',
        'Cultura',
        'Deporte',
        'Día de la comarca',
        'Economía',
        'Fauna',
        'Fiestas',
        'Gastronomía',
        'Música',
        'Naturaleza',
        'Política',
        'Religión',
        'Sociedad',
        'Sucesos',
        'Ternera de aliste',
        'Tradiciones',
        'Turismo',
    ];

    /**
     * Tipos de servicio tal y como aparecen en aliste.info.
     */
    private const SERVICIOS = [
        'Abogados',
        'Abonos y Fertilizantes',
        'Agricultura',
        'Al Por Mayor',
        'Alimentación',
        'Almacén de bebidas',
        'Almacén de construcción',
        'Alojamiento',
        'Artesanía',
        'Asesoría',
        'Asociación Cultural',
        'Autobuses',
        'Ayuntamiento',
        'Banco del tiempo',
        'Bancos-Cajas',
        'Bolsa',
        'Cabañas',
        'Café-Bar',
        'Calefacción',
        'Camping',
        'Carnicería',
        'Carpintería',
        'Casa Rural',
        'Centro Médico',
        'Cereales',
        'Cerrajería-Ferrallistas',
        'Colegios',
        'Comedor Social',
        'Comisaría',
        'Construcción',
        'Cooperativa',
        'Cristalería',
        'Cruz Roja',
        'Electricistas',
        'Estancos',
        'Farmacias',
        'Floristería',
        'Fontanerías',
        'Frutería',
        'Ganadería',
        'Ganadero',
        'Gas, propano, gasóleos',
        'Gasolinera',
        'Grupos de Acción Local',
        'Guardia Civil',
        'Hormigones',
        'Hotel',
        'Iglesia Católica',
        'Industrial',
        'Informática',
        'Instituto',
        'Masajistas',
        'Mercerías',
        'Muebles',
        'Museo',
        'Notarios',
        'Organismo Oficial',
        'Panaderías',
        'Peluquerías',
        'Pescaderías',
        'Piedras Naturales',
        'Piscina',
        'Plantas Medicinales',
        'Quincallería',
        'Representantes',
        'Residencias 3ª Edad',
        'Restaurantes',
        'Ropa y Accesorios',
        'Seguros',
        'Semillería',
        'Servicios Funerarios',
        'Setas',
        'Sondeos',
        'Supermercado',
        'Talleres y Mecánica',
        'Taxis',
        'Transporte',
        'Veterinarios',
        'Yesistas',
        'Zapaterías',
    ];

    /**
     * Etiquetas para puntos de interés (no publicadas como listado cerrado en la
     * web original, propuestas a partir de los puntos de interés reales vistos).
     */
    private const PUNTOS_INTERES = [
        'Iglesia',
        'Ermita',
        'Monumento',
        'Fuente',
        'Mirador',
        'Yacimiento arqueológico',
        'Puente',
        'Molino',
        'Museo',
        'Área recreativa',
        'Polideportivo',
        'Piscina natural',
        'Naturaleza',
    ];

    /**
     * Géneros musicales propuestos para la nueva sección de Música.
     */
    private const CANCIONES = [
        'Folclore',
        'Música Tradicional',
        'Rondalla',
        'Copla',
        'Pandereta y Tamboril',
        'Dulzaina',
        'Música Moderna',
        'Himnos y Jotas',
    ];

    /**
     * Géneros literarios propuestos para la nueva sección de Literatura.
     */
    private const OBRAS_LITERARIAS = [
        'Poesía',
        'Relato',
        'Novela',
        'Ensayo',
        'Leyenda',
        'Tradición Oral',
    ];

    public function run(): void
    {
        $this->crear(self::NOTICIAS, 'noticia');
        $this->crear(self::SERVICIOS, 'servicio');
        $this->crear(self::PUNTOS_INTERES, 'punto_interes');
        $this->crear(self::CANCIONES, 'cancion');
        $this->crear(self::OBRAS_LITERARIAS, 'obra_literaria');
    }

    /**
     * @param  array<int, string>  $nombres
     */
    private function crear(array $nombres, string $grupo): void
    {
        foreach ($nombres as $nombre) {
            Categoria::updateOrCreate(
                ['slug' => Str::slug($nombre), 'grupo' => $grupo],
                ['nombre' => $nombre]
            );
        }
    }
}
