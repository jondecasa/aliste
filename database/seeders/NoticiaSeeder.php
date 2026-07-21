<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\Pueblo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoticiaSeeder extends Seeder
{
    /**
     * Resumen original de noticias reales publicadas por medios locales
     * (Zamora News, ZA49) sobre pueblos de la comarca de Aliste, con enlace
     * a la fuente original.
     */
    private const NOTICIAS = [
        [
            'pueblo' => 'San Cristóbal',
            'titulo' => 'San Cristóbal de Aliste prepara tres días de fiestas patronales',
            'extracto' => 'La Asociación El Balcón de Aliste organiza del 24 al 26 de julio una celebración que combina actos religiosos con música en directo, circo y una tradicional guerra de agua.',
            'cuerpo' => '<p>San Cristóbal de Aliste celebra sus fiestas patronales del 24 al 26 de julio, con un programa que mezcla tradición y ocio para todas las edades. La Asociación El Balcón de Aliste ha organizado tres jornadas con procesión, degustaciones populares y una guerra de agua, además de actuaciones musicales como la del grupo de pop-rock Efecto Retroactivo y un espectáculo de circo.</p><p>Las fiestas se cerrarán con verbena a cargo de DJ y una exhibición de folclore tradicional, en una cita que cada año reúne a vecinos y visitantes de toda la comarca.</p>',
            'categorias' => ['Fiestas', 'Tradiciones'],
            'fuente_nombre' => 'Zamora News',
            'fuente_url' => 'https://www.zamoranews.com/articulo/comarcas/san-cristobal-aliste-presenta-fiestas-patronales-tres-dias-musica-tradicion/20260721175603404644.html',
            'imagen_url' => 'https://www.zamoranews.com/media/zamoranews/images/2026/07/21/2026072117543869314.jpg',
            'publicado_en' => '2026-07-21',
        ],
        [
            'pueblo' => 'Grisuela',
            'titulo' => 'Grisuela de Aliste ultima los preparativos de sus fiestas mayores',
            'extracto' => 'La localidad celebrará del 16 al 22 de julio los actos en honor a Santa María Magdalena, con conciertos, concursos populares y una gran verbena de clausura.',
            'cuerpo' => '<p>Grisuela de Aliste se prepara para vivir sus fiestas mayores en honor a Santa María Magdalena, que se prolongarán del 16 al 22 de julio. El programa combina actos religiosos con música en directo, con las actuaciones de los grupos La Huella y Radar, además de sesiones de DJ.</p><p>No faltarán las competiciones tradicionales, como el campeonato de tute o el concurso de lanzamiento de la "patarra", ni las comidas populares y las actividades infantiles. La clausura, el 22 de julio, contará con gaita, baile folclórico y la actuación del grupo Pikante.</p>',
            'categorias' => ['Fiestas', 'Tradiciones'],
            'fuente_nombre' => 'Zamora News',
            'fuente_url' => 'https://www.zamoranews.com/articulo/comarcas/cuenta-atras-fiestas-grisuela-aliste-ha-comenzado/20260715093141403460.html',
            'imagen_url' => 'https://www.zamoranews.com/media/zamoranews/images/2025/07/23/2025072310544081713.jpg',
            'publicado_en' => '2026-07-15',
        ],
        [
            'pueblo' => 'Nuez',
            'titulo' => 'Nueva fase administrativa para la concentración parcelaria de Nuez de Aliste',
            'extracto' => 'La Junta de Castilla y León abre un nuevo plazo de exposición pública para que los propietarios afectados revisen las modificaciones incorporadas al proyecto antes de su aprobación definitiva.',
            'cuerpo' => '<p>El proceso de concentración parcelaria de Nuez de Aliste avanza hacia una nueva fase administrativa. La Junta de Castilla y León ha abierto un plazo de diez días para que los propietarios afectados por las modificaciones introducidas tras anteriores alegaciones puedan consultar la documentación y presentar, si lo consideran necesario, nuevas objeciones.</p><p>El expediente puede consultarse previa cita en las oficinas de Proinser Zamora 2000. El objetivo de este trámite es garantizar que todos los afectados conozcan los cambios incorporados al proyecto antes de que reciba su aprobación definitiva.</p>',
            'categorias' => ['Economía'],
            'fuente_nombre' => 'Zamora News',
            'fuente_url' => 'https://www.zamoranews.com/articulo/47-comarcas/reordenacion-fincas-nuez-aliste-encara-nueva-fase-administrativa/20260713100923403159.html',
            'imagen_url' => 'https://www.zamoranews.com/media/zamoranews/images/2021/05/25/2021052512310361199.jpg',
            'publicado_en' => '2026-07-13',
        ],
        [
            'pueblo' => 'San Vitero',
            'titulo' => 'San Vitero celebra la VI edición de su Cross Popular',
            'extracto' => 'La prueba, con recorridos de 10 y 5 kilómetros, se disputó el 11 de julio combinando deporte, naturaleza y convivencia vecinal, con criterios de sostenibilidad en los avituallamientos.',
            'cuerpo' => '<p>San Vitero acogió el 11 de julio la sexta edición de su Cross Popular, una cita deportiva que combina una carrera de 10 kilómetros, con salida a las 9:30 horas, y una marcha de 5 kilómetros a partir de las 10:00 horas, ambas con salida desde el recinto ferial del pueblo.</p><p>La inscripción, con un coste de 20 euros, se cerró el 1 de julio e incluyó dorsal, recuerdo de la prueba y comida de convivencia para los participantes. La organización apostó por la sostenibilidad, prohibiendo el uso de vasos de plástico en los puntos de avituallamiento.</p>',
            'categorias' => ['Deporte'],
            'fuente_nombre' => 'Zamora News',
            'fuente_url' => 'https://www.zamoranews.com/articulo/47-comarcas/san-vitero-celebra-11-julio-vi-edicion-cross-popular-deporte-naturaleza-convivencia-aliste/20260612073851396838.html',
            'imagen_url' => 'https://www.zamoranews.com/media/zamoranews/images/2026/06/12/2026061207375697672.jpg',
            'publicado_en' => '2026-06-12',
        ],
        [
            'pueblo' => 'Valer',
            'titulo' => 'Valer de Aliste, anfitriona de la XXXV Fiesta de la Comarca Aliste, Tábara y Alba',
            'extracto' => 'El municipio acogió los días 3 y 4 de julio esta cita itinerante que cada año recuerda la identidad compartida de las tres comarcas zamoranas.',
            'cuerpo' => '<p>Valer de Aliste tomó el relevo como sede de la XXXV Fiesta de la Comarca Aliste, Tábara y Alba, celebrada el 3 y 4 de julio. La jornada incluyó un mercado de artesanía, exhibiciones de trilla tradicional y la recreación de un carro de bodas alistano.</p><p>Esta cita, que rota cada año entre localidades de las tres comarcas, se ha consolidado como un auténtico día de pertenencia para sus habitantes, y busca preservar el patrimonio cultural del mundo rural zamorano.</p>',
            'categorias' => ['Fiestas', 'Tradiciones', 'Día de la comarca'],
            'fuente_nombre' => 'Zamora News',
            'fuente_url' => 'https://www.zamoranews.com/articulo/47-comarcas/valer-aliste-toma-relevo-como-anfitriona-xxxv-fiesta-comarca-aliste-tabara-alba/20260625175603399744.html',
            'imagen_url' => 'https://www.zamoranews.com/media/zamoranews/images/2026/06/25/2026062517530482978.jpg',
            'publicado_en' => '2026-06-25',
        ],
        [
            'pueblo' => 'Castro de Alcañices',
            'titulo' => 'Castro de Alcañices prepara un intenso fin de semana de fiestas',
            'extracto' => 'La localidad celebra del 24 al 26 de julio los actos en honor a Santiago Apóstol y Santa Ana, con flamenco, tiro al plato, bailes regionales y actividades infantiles.',
            'cuerpo' => '<p>Castro de Alcañices vivirá del 24 al 26 de julio sus fiestas patronales en honor a Santiago Apóstol y Santa Ana. El programa reúne actuaciones de flamenco, sesiones de DJ, un concurso de tiro al plato, actividades infantiles, bailes regionales y servicio de comidas.</p><p>El fin de semana está pensado como un punto de encuentro para vecinos y visitantes que buscan disfrutar del verano combinando tradición, deporte, música y gastronomía.</p>',
            'categorias' => ['Fiestas'],
            'fuente_nombre' => 'ZA49',
            'fuente_url' => 'https://www.za49.es/aliste/buscas-escapada-fin-semana-mira-todo-preparado-castro-alcanices_1_5960769.html',
            'imagen_url' => 'https://www.za49.es/images/showid/8072316',
            'publicado_en' => '2026-07-21',
        ],
        [
            'pueblo' => 'Río Manzanas',
            'titulo' => 'Río Manzanas y Figueruela de Arriba estrenan mejoras en agua potable y sanidad',
            'extracto' => 'Río Manzanas cuenta ya con una nueva estación de tratamiento de agua y Figueruela de Arriba con un consultorio médico accesible y eficiente energéticamente.',
            'cuerpo' => '<p>Dos pueblos de Aliste han visto cumplida una reivindicación histórica. Río Manzanas dispone ya de una nueva estación de tratamiento que garantiza un suministro de agua potable más seguro, eficiente y continuo, mientras que Figueruela de Arriba estrena un consultorio médico con mejoras de accesibilidad y sistemas energéticamente eficientes.</p><p>Ambas localidades han incorporado también nuevos espacios recreativos, con un parque infantil y una zona de ejercicios al aire libre. El presidente de la Diputación visitó los pueblos con motivo de la inauguración de las obras.</p>',
            'categorias' => ['Sociedad'],
            'fuente_nombre' => 'ZA49',
            'fuente_url' => 'https://www.za49.es/aliste/cambio-reclamaban-riomanzanas-figueruela-arriba-esta-aqui-agua-potable-segura-nuevo-consultorio_1_5960893.html',
            'imagen_url' => 'https://www.za49.es/images/showid/8072434',
            'publicado_en' => '2026-07-21',
        ],
        [
            'pueblo' => 'Sejas',
            'titulo' => 'Sejas de Aliste acoge una exposición itinerante con nueve artistas de la comarca',
            'extracto' => "La muestra 'Arte Aliste' reinterpreta la identidad alistana a través de la piedra, el hierro, la cerámica y la madera, y viajará después a Alcañices.",
            'cuerpo' => '<p>La Sala Ricardo Segundo de Sejas de Aliste acoge desde el 25 de julio y hasta el 11 de agosto una exposición que reúne el trabajo de nueve artistas de la comarca, cada uno de ellos reinterpretando un mismo símbolo identitario a través de materiales como la piedra, el hierro, la cerámica o la madera.</p><p>Tras su paso por Sejas, la muestra viajará a la Sala Los Toriles de Alcañices, donde podrá visitarse del 12 al 30 de agosto. La iniciativa cuenta con el respaldo de los ayuntamientos y entidades culturales de ambas localidades.</p>',
            'categorias' => ['Cultura'],
            'fuente_nombre' => 'ZA49',
            'fuente_url' => 'https://www.za49.es/aliste/arte-autentico-aliste-reune-exposicion-unica-recorrera-pueblos-zamora_1_5959008.html',
            'imagen_url' => 'https://www.za49.es/images/showid/8070379',
            'publicado_en' => '2026-07-20',
        ],
        [
            'pueblo' => 'Latedo',
            'titulo' => 'Latedo celebra sus fiestas en honor a Santiago Apóstol',
            'extracto' => 'La localidad, en el municipio de Trabazos, vive el 24 y 25 de julio dos jornadas de misa, procesión, magia, juegos tradicionales y baile con la Orquesta Apolo.',
            'cuerpo' => '<p>Latedo celebra el 24 y 25 de julio sus fiestas patronales en honor a Santiago Apóstol. El sábado, a las 13:00 horas, tendrán lugar la misa solemne y la procesión, a las que seguirán actividades de animación infantil, pintacaras, magia y juegos tradicionales.</p><p>La jornada se completará con degustaciones populares y baile a cargo de la Orquesta Apolo, en una celebración que reúne cada año a los vecinos de esta localidad del municipio de Trabazos.</p>',
            'categorias' => ['Fiestas', 'Tradiciones'],
            'fuente_nombre' => 'ZA49',
            'fuente_url' => 'https://www.za49.es/aliste/pequeno-pueblo-zamora-tiene-listas-fiestas-santiago-dias-tradicion-musica-actividades-todas-edades_1_5956823.html',
            'imagen_url' => 'https://www.za49.es/images/showid/8067693',
            'publicado_en' => '2026-07-20',
        ],
        [
            'pueblo' => 'Riofrío',
            'titulo' => 'Riofrío programa un agosto repleto de actividades para todas las edades',
            'extracto' => 'La asociación cultural Riofrío Despierta organiza talleres de adobe, una limpieza del río y una gran jornada el 15 de agosto con hinchables, teatro y cena popular.',
            'cuerpo' => '<p>La asociación cultural Riofrío Despierta ha preparado un mes de agosto cargado de actividades para el pueblo. El 25 de julio se celebrará un taller de fabricación de adobe y el 8 de agosto una jornada de limpieza del río, previa a la gran cita del 15 de agosto.</p><p>Ese día, la localidad acogerá hinchables para los más pequeños, la representación teatral "Una de detectives", una cena popular con gazpacho y otros platos, y una sesión de bingo. Las inscripciones pueden realizarse hasta el 8 de agosto a través de WhatsApp o en un comercio local.</p>',
            'categorias' => ['Cultura', 'Tradiciones'],
            'fuente_nombre' => 'ZA49',
            'fuente_url' => 'https://www.za49.es/aliste/pueblo-zamora-prepara-agosto-no-salir-teatro-cena-gratis-hinchables-bingo-hasta-horno-tradicional_1_5955403.html',
            'imagen_url' => 'https://www.za49.es/images/showid/8066106',
            'publicado_en' => '2026-07-16',
        ],
    ];

    public function run(): void
    {
        foreach (self::NOTICIAS as $datos) {
            $pueblo = Pueblo::where('nombre', $datos['pueblo'])->first();

            if (! $pueblo) {
                $this->command?->warn("Pueblo no encontrado: {$datos['pueblo']}");

                continue;
            }

            $noticia = Noticia::updateOrCreate(
                ['slug' => Str::slug($datos['titulo'])],
                [
                    'pueblo_id' => $pueblo->id,
                    'titulo' => $datos['titulo'],
                    'extracto' => $datos['extracto'],
                    'cuerpo' => $datos['cuerpo'],
                    'fuente_nombre' => $datos['fuente_nombre'],
                    'fuente_url' => $datos['fuente_url'],
                    'url_externa' => $datos['fuente_url'],
                    'publicado_en' => $datos['publicado_en'],
                ]
            );

            $categoriaIds = Categoria::deGrupo('noticia')
                ->whereIn('nombre', $datos['categorias'])
                ->pluck('id');

            $noticia->categorias()->sync($categoriaIds);

            if (! $noticia->imagen_portada && isset($datos['imagen_url'])) {
                if ($ruta = $this->descargarImagen($datos['imagen_url'], $noticia->slug)) {
                    $noticia->update(['imagen_portada' => Storage::disk('public')->url($ruta)]);
                }
            }
        }
    }

    private function descargarImagen(string $url, string $slug): ?string
    {
        try {
            $respuesta = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
        } catch (\Throwable) {
            $this->command?->warn("No se pudo descargar la imagen: {$url}");

            return null;
        }

        if (! $respuesta->successful()) {
            $this->command?->warn("No se pudo descargar la imagen ({$respuesta->status()}): {$url}");

            return null;
        }

        $cuerpo = $respuesta->body();
        $extension = match (true) {
            str_starts_with($cuerpo, "\x89PNG") => 'png',
            str_starts_with($cuerpo, "\xFF\xD8\xFF") => 'jpg',
            default => null,
        };

        if (! $extension) {
            $this->command?->warn("La URL no devolvió una imagen reconocible: {$url}");

            return null;
        }

        $ruta = "noticias/{$slug}.{$extension}";
        Storage::disk('public')->put($ruta, $cuerpo);

        return $ruta;
    }
}
