<?php

namespace Database\Seeders;

use App\Models\AudioCancion;
use App\Models\Cancion;
use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Canciones de ejemplo cargadas desde el panel de administración, con sus
 * portadas y archivos de audio reales (guardados en data/canciones/).
 */
class CancionesSeeder extends Seeder
{
    private const RUTA_DATOS = __DIR__.'/data/canciones';

    public function run(): void
    {
        $DESCRIPCION_1 = <<<'DESCRIPCION_1'
<p class="PDq2pG_selectionAnchorContainer" data-start="82" data-end="462">Pocas canciones populares representan tan bien la estrecha relaci&oacute;n cultural entre Sanabria, Galicia y el nordeste de Portugal como <strong data-start="214" data-end="239">El Mandil de Carolina</strong>. Transmitida de generaci&oacute;n en generaci&oacute;n, esta melod&iacute;a ha cruzado fronteras sin perder su esencia, convirti&eacute;ndose en uno de los ejemplos m&aacute;s conocidos del patrimonio musical compartido del noroeste de la Pen&iacute;nsula Ib&eacute;rica.</p>
<p data-start="464" data-end="734">Aunque en Sanabria es conocida como <strong data-start="500" data-end="525">El Mandil de Carolina</strong>, en Galicia recibe el nombre de <strong data-start="558" data-end="580">A saia da Carolina</strong> y, en la Tierra de Miranda (Portugal), tambi&eacute;n se interpreta en lengua mirandesa con el mismo t&iacute;tulo. Tres territorios, tres lenguas y una misma melod&iacute;a.</p>
<h2 class="PDq2pG_selectionAnchorContainer" data-section-id="17mrj6h" data-start="741" data-end="790">Una de las canciones m&aacute;s populares de Sanabria</h2>
<p data-start="792" data-end="1071">Durante d&eacute;cadas, <strong data-start="809" data-end="834">El Mandil de Carolina</strong> ha formado parte del repertorio tradicional sanabr&eacute;s. Era habitual escucharla en fiestas populares, reuniones familiares y celebraciones, acompa&ntilde;ada por instrumentos tradicionales como la <strong data-start="1023" data-end="1042">gaita sanabresa</strong>, el tamboril o la pandereta.</p>
<p data-start="1073" data-end="1391">Su letra, sencilla y alegre, gira en torno al mandil o la falda de Carolina, decorados con dibujos de animales y flores. Como ocurre con muchas canciones populares, el verdadero protagonismo no es la historia en s&iacute;, sino el ritmo repetitivo y pegadizo que la convierte en una pieza ideal para cantar y bailar en grupo.</p>
<p data-start="1393" data-end="1590">Adem&aacute;s, algunas versiones conservan rasgos del antiguo <strong data-start="1448" data-end="1467">habla sanabresa</strong>, una variedad del dominio ling&uuml;&iacute;stico asturleon&eacute;s que a&uacute;n pervive en determinadas expresiones y vocabulario de la comarca.</p>
<hr data-start="1592" data-end="1595">
<h2 data-section-id="19n4cau" data-start="1597" data-end="1623">&iquest;D&oacute;nde naci&oacute; realmente?</h2>
<p data-start="1625" data-end="1807">Determinar el origen exacto de una canci&oacute;n tradicional no siempre es posible. Al transmitirse oralmente durante generaciones, las melod&iacute;as evolucionan y se adaptan a cada territorio.</p>
<p data-start="1809" data-end="1883">En el caso de <strong data-start="1823" data-end="1848">El Mandil de Carolina</strong>, existen tres grandes tradiciones:</p>
<ul data-start="1885" data-end="2119">
<li data-section-id="1ekqq69" data-start="1885" data-end="1969"><strong data-start="1887" data-end="1899">Sanabria</strong>, donde forma parte del repertorio folkl&oacute;rico desde hace generaciones.</li>
<li data-section-id="38nxfa" data-start="1970" data-end="2035"><strong data-start="1972" data-end="1983">Galicia</strong>, donde es muy conocida como <strong data-start="2012" data-end="2034">A saia da Carolina</strong>.</li>
<li data-section-id="1rur1wc" data-start="2036" data-end="2119"><strong data-start="2038" data-end="2069">Miranda do Douro (Portugal)</strong>, donde tambi&eacute;n se interpreta en lengua mirandesa.</li>
</ul>
<p data-start="2121" data-end="2537">Diversos investigadores consideran que la versi&oacute;n mirandesa podr&iacute;a encontrarse entre las m&aacute;s antiguas conservadas, aunque no existe un consenso definitivo sobre cu&aacute;l fue el punto de origen. Lo que s&iacute; parece claro es que la canci&oacute;n se difundi&oacute; por un territorio donde las relaciones entre pueblos eran constantes y las fronteras pol&iacute;ticas apenas imped&iacute;an el intercambio cultural.</p>
<hr data-start="2539" data-end="2542">
<h2 data-section-id="8rf7v0" data-start="2544" data-end="2586">Una muestra de un patrimonio compartido</h2>
<p data-start="2588" data-end="2770">Lejos de pertenecer exclusivamente a una comarca, <strong data-start="2638" data-end="2663">El Mandil de Carolina</strong> refleja la profunda conexi&oacute;n hist&oacute;rica entre los pueblos del occidente zamorano, Galicia y Tr&aacute;s-os-Montes.</p>
<p data-start="2772" data-end="3055">Durante siglos, comerciantes, pastores, arrieros y familias cruzaron continuamente estas tierras, compartiendo costumbres, bailes, romances y canciones. Por ello, no resulta extra&ntilde;o encontrar melod&iacute;as pr&aacute;cticamente id&eacute;nticas interpretadas en castellano, sanabr&eacute;s, gallego o mirand&eacute;s.</p>
<p data-start="3057" data-end="3261">Este fen&oacute;meno es especialmente visible en la m&uacute;sica tradicional de la llamada <strong data-start="3135" data-end="3143">Raya</strong>, donde muchas piezas sobreviven con peque&ntilde;as variaciones de letra, pero conservando pr&aacute;cticamente intacta su melod&iacute;a.</p>
<hr data-start="3263" data-end="3266">
<h2 data-section-id="173i86u" data-start="3268" data-end="3310">La gaita, otro nexo entre estas tierras</h2>
<p data-start="3312" data-end="3384">Si hay un instrumento que simboliza esta uni&oacute;n cultural es la <strong data-start="3374" data-end="3383">gaita</strong>.</p>
<p data-start="3386" data-end="3710">La <strong data-start="3389" data-end="3408">gaita sanabresa</strong>, con caracter&iacute;sticas propias que la diferencian de otros modelos, contin&uacute;a siendo protagonista en romer&iacute;as, mascaradas y fiestas populares de la comarca. Su sonido acompa&ntilde;a frecuentemente a <strong data-start="3599" data-end="3624">El Mandil de Carolina</strong>, tanto en las interpretaciones m&aacute;s tradicionales como en adaptaciones contempor&aacute;neas.</p>
<p data-start="3712" data-end="4006">En los &uacute;ltimos a&ntilde;os, diversos grupos de m&uacute;sica folk han recuperado esta canci&oacute;n, fusionando la instrumentaci&oacute;n tradicional con guitarras, bater&iacute;a o bajo el&eacute;ctrico. Gracias a estas nuevas versiones, una melod&iacute;a con siglos de historia sigue llegando a nuevas generaciones sin perder su identidad.</p>
<hr data-start="4008" data-end="4011">
<h2 data-section-id="x14yzk" data-start="4013" data-end="4042">Una canci&oacute;n que sigue viva</h2>
<p data-start="4044" data-end="4258">A diferencia de otras composiciones tradicionales que han ca&iacute;do en el olvido, <strong data-start="4122" data-end="4147">El Mandil de Carolina</strong> contin&uacute;a interpret&aacute;ndose en festivales de folklore, encuentros de gaiteros y conciertos de m&uacute;sica tradicional.</p>
<p data-start="4260" data-end="4657">Su popularidad tambi&eacute;n ha propiciado que existan numerosas versiones grabadas por artistas y grupos de distintos lugares de Espa&ntilde;a y Portugal, adem&aacute;s de aparecer recogida en importantes archivos de m&uacute;sica tradicional como el <strong data-start="4485" data-end="4529">Fondo de M&uacute;sica Tradicional del IMF-CSIC</strong>, donde pueden consultarse distintas variantes recopiladas en varias provincias espa&ntilde;olas.</p>
<hr data-start="4659" data-end="4662">
<h2 data-section-id="nu86eu" data-start="4664" data-end="4714">Un s&iacute;mbolo de la identidad del noroeste ib&eacute;rico</h2>
<p data-start="4716" data-end="4943">M&aacute;s all&aacute; de su pegadiza melod&iacute;a, <strong data-start="4749" data-end="4774">El Mandil de Carolina</strong> representa una forma de entender la cultura popular: una tradici&oacute;n compartida que ha sobrevivido gracias a la memoria de quienes la cantaron generaci&oacute;n tras generaci&oacute;n.</p>
<p data-start="4945" data-end="5250" data-is-last-node="" data-is-only-node="">Sanabria, Galicia y Miranda do Douro conservan hoy distintas versiones de una misma canci&oacute;n, record&aacute;ndonos que el patrimonio cultural no entiende de fronteras. Cada interpretaci&oacute;n aporta peque&ntilde;os matices, pero todas mantienen vivo un legado com&uacute;n que sigue formando parte de la identidad de estas tierras.</p>
DESCRIPCION_1;

        $LETRA_1 = <<<'LETRA_1'
El mandil de Carolina
tiene un lagarto pintado;
cuando Carolina baila,
el lagarto menea el rabo.

¿Bailaste Carolina?
Bailé si señor.
Dime con quien bailaste.
Bailé con miño amor

Bailé con miño amor
Bailé con miño amor.
¿Bailaste Carolina?
Bailé, si señor.

El zapato pide media
la media pide zapato;
una muchiquina guapa,
también pide un chaval guapo.
¿Bailaste Carolina?
Bailé …...

Bailé con miño….

El rey señor cando canta
mete el rabo entre a sudeira;
también yo lo metería,
en una chica soltera.

¿Bailaste Carolina?
Bailé si señor.
Dime con quien bailaste.
Bailé con miño amor

Bailé con miño amor
Bailé con miño amor.
¿Bailaste Carolina?
Bailé, si señor
LETRA_1;

        $DESCRIPCION_2 = <<<'DESCRIPCION_2'
<p class="PDq2pG_selectionAnchorContainer" data-start="87" data-end="529">&nbsp;</p>
<p class="PDq2pG_selectionAnchorContainer" data-start="87" data-end="529"><iframe title="YouTube video player" src="https://www.youtube.com/embed/EfRlAqMbt5Q?si=OXYozVy4RGX4MeIy" width="560" height="315" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen="allowfullscreen" referrerpolicy="strict-origin-when-cross-origin"></iframe></p>
<p class="PDq2pG_selectionAnchorContainer" data-start="87" data-end="529">Entre las canciones tradicionales m&aacute;s conocidas de Sanabria se encuentra <strong data-start="160" data-end="185">&iquest;D&oacute;nde vas, Adelaida?</strong>, una pieza popular que ha pasado de generaci&oacute;n en generaci&oacute;n y que a&uacute;n hoy forma parte del repertorio del folklore del noroeste peninsular. Su melod&iacute;a sencilla, su estructura repetitiva y su car&aacute;cter narrativo han permitido que sobreviva durante siglos, dando lugar a numerosas variantes repartidas por distintas provincias de Castilla y Le&oacute;n.</p>
<p data-start="531" data-end="904">Aunque suele asociarse a Sanabria, la realidad es que <strong data-start="585" data-end="610">&iquest;D&oacute;nde vas, Adelaida?</strong> es una canci&oacute;n ampliamente difundida por el antiguo Reino de Le&oacute;n. Existen versiones documentadas en Zamora, Le&oacute;n, Salamanca y Palencia, lo que demuestra que se trata de una composici&oacute;n popular compartida por buena parte del occidente castellano y leon&eacute;s.</p>
<hr data-start="906" data-end="909">
<h2 data-section-id="p1m898" data-start="911" data-end="945">Una canci&oacute;n de amor y desenga&ntilde;o</h2>
<p data-start="947" data-end="1043">La historia que narra la canci&oacute;n es sencilla, como ocurre en muchas composiciones tradicionales.</p>
<p data-start="1045" data-end="1309">Adelaida emprende la b&uacute;squeda de su enamorado, normalmente llamado <strong data-start="1112" data-end="1123">Enrique</strong>, con la intenci&oacute;n de hablar con &eacute;l. Sin embargo, conforme avanza la letra descubre que &eacute;l no aparece y que otras personas le advierten de que podr&iacute;a haberla olvidado o incluso enga&ntilde;ado.</p>
<p data-start="1311" data-end="1742">El desenlace cambia seg&uacute;n la versi&oacute;n, pero casi todas terminan con una reflexi&oacute;n cargada de resignaci&oacute;n: el verdadero temor de Adelaida no es perder a su enamorado, sino quedarse soltera y no llegar nunca a casarse. Este tipo de tem&aacute;tica era muy habitual en la tradici&oacute;n oral, donde las canciones serv&iacute;an para reflejar las preocupaciones, costumbres y valores de la sociedad rural de la &eacute;poca.</p>
<hr data-start="1744" data-end="1747">
<h2 data-section-id="1f8o9aj" data-start="1749" data-end="1784">Una canci&oacute;n con muchas versiones</h2>
<p data-start="1786" data-end="1897">Uno de los aspectos m&aacute;s interesantes de <strong data-start="1826" data-end="1851">&iquest;D&oacute;nde vas, Adelaida?</strong> es la gran cantidad de variantes que existen.</p>
<p data-start="1899" data-end="1978">Dependiendo del pueblo o de la comarca cambian algunos detalles de la historia:</p>
<ul data-start="1980" data-end="2333">
<li data-section-id="prfb68" data-start="1980" data-end="2058">El nombre del enamorado puede ser <strong data-start="2016" data-end="2027">Enrique</strong>, <strong data-start="2029" data-end="2040">Alfonso</strong> u otro diferente.</li>
<li data-section-id="12qr6rt" data-start="2059" data-end="2124">La hora que espera Adelaida var&iacute;a entre unas versiones y otras.</li>
<li data-section-id="63i6fa" data-start="2125" data-end="2191">Algunos versos modifican el desenlace o a&ntilde;aden nuevos episodios.</li>
<li data-section-id="ignzxf" data-start="2192" data-end="2333">En determinadas localidades la canci&oacute;n se utilizaba como canto de baile, mientras que en otras pas&oacute; a formar parte del repertorio infantil.</li>
</ul>
<p data-start="2335" data-end="2656">Esta diversidad es consecuencia de la transmisi&oacute;n oral. Antes de que existieran grabaciones o cancioneros impresos, cada generaci&oacute;n aprend&iacute;a las canciones escuch&aacute;ndolas, por lo que era habitual que cada pueblo introdujera peque&ntilde;as modificaciones sin alterar la esencia de la melod&iacute;a.</p>
<hr data-start="2658" data-end="2661">
<h2 data-section-id="m0c9ip" data-start="2663" data-end="2703">Presente en toda la tradici&oacute;n leonesa</h2>
<p data-start="2705" data-end="2899">Aunque en Sanabria sigue siendo una de las canciones m&aacute;s reconocibles, las recopilaciones etnogr&aacute;ficas demuestran que <strong data-start="2823" data-end="2848">&iquest;D&oacute;nde vas, Adelaida?</strong> estaba presente en un territorio mucho m&aacute;s amplio.</p>
<p data-start="2901" data-end="3323">El <strong data-start="2904" data-end="2948">Fondo de M&uacute;sica Tradicional del IMF-CSIC</strong> conserva grabaciones realizadas en localidades de Zamora, Le&oacute;n, Salamanca y Palencia, registradas entre las d&eacute;cadas de 1980 y 1990 a partir de informantes que todav&iacute;a recordaban la canci&oacute;n aprendida de sus mayores. Estas recopilaciones constituyen un valioso testimonio del patrimonio musical transmitido oralmente durante generaciones.</p>
<hr data-start="3325" data-end="3328">
<h2 data-section-id="1utuvx4" data-start="3330" data-end="3374">De los filandones a las fiestas populares</h2>
<p data-start="3376" data-end="3497">Como muchas canciones tradicionales sanabresas, <strong data-start="3424" data-end="3449">&iquest;D&oacute;nde vas, Adelaida?</strong> no naci&oacute; para ser escuchada desde un escenario.</p>
<p data-start="3499" data-end="3835">Era una canci&oacute;n que se interpretaba en reuniones familiares, filandones, fiestas patronales y bailes populares, acompa&ntilde;ada por instrumentos tradicionales como la gaita, el tamboril o la pandereta. Su ritmo sencillo facilitaba que cualquier persona pudiera aprenderla y cantarla, contribuyendo as&iacute; a su conservaci&oacute;n durante generaciones.</p>
<p data-start="3837" data-end="4163">Con el paso del tiempo tambi&eacute;n fue incorporada a cancioneros populares y grabada por int&eacute;rpretes especializados en m&uacute;sica tradicional, entre ellos <strong data-start="3984" data-end="4000">Joaqu&iacute;n D&iacute;az</strong>, cuya recopilaci&oacute;n <em data-start="4020" data-end="4043">Canciones de Sanabria</em> contribuy&oacute; a difundir parte del repertorio musical de la comarca fuera de Zamora.</p>
<hr data-start="4165" data-end="4168">
<h2 data-section-id="ikp9rc" data-start="4170" data-end="4215">Un ejemplo del patrimonio musical sanabr&eacute;s</h2>
<p data-start="4217" data-end="4430">M&aacute;s all&aacute; de su letra, <strong data-start="4239" data-end="4264">&iquest;D&oacute;nde vas, Adelaida?</strong> representa una forma de entender la m&uacute;sica popular: canciones transmitidas de padres a hijos, adaptadas por cada pueblo y conservadas gracias a la memoria colectiva.</p>
<p data-start="4432" data-end="4760" data-is-last-node="" data-is-only-node="">Hoy sigue interpret&aacute;ndose en encuentros de folklore, festivales y actuaciones de m&uacute;sica tradicional, recordando la riqueza cultural de Sanabria y del antiguo territorio leon&eacute;s. Cada versi&oacute;n conserva peque&ntilde;os matices propios, pero todas mantienen viva una melod&iacute;a que forma parte del patrimonio inmaterial del noroeste de Espa&ntilde;a.</p>
DESCRIPCION_2;

        $LETRA_2 = <<<'LETRA_2'
¿Dónde vas, dónde vas Adelaida?
¿Dónde vas, dónde vas por ahí?
Voy en busca de mi amante Enrique (x2)
Que se ha vuelto loco llorando por mí 

Son las once y Enrique no viene
Son las once y Enrique no está
Mis amigos me andan diciendo (x2)
Que mi amante Enrique me quiere olvidar

¿Cómo es esto, cómo va a ser esto?
¿Cómo es esto, cómo lo será?
Yo me pongo mi traje de gala (x2)
Y al pie de la iglesia, lo voy a esperar

Al entrar en el patio adentro
Yo veo un hombre al pie del altar
Le pregunto ¿Qué haces Enrique? (x2)
Y él me contesta que se va a casar

Yo no creo en los hombres traidores
Ni tampoco en las olas del mar
Lo que siento es quedarme soltera (x2)
Para nunca nunca, volverme a casar
LETRA_2;

        $cancion1 = Cancion::updateOrCreate(
            ['slug' => 'el-mandil-de-carolina'],
            [
                'titulo' => 'El mandil de Carolina',
                'artista' => 'Desconocido',
                'album' => 'Desconocido',
                'anio' => 1920,
                'duracion' => 248,
                'portada' => $this->copiarAFichero('el-mandil-de-carolina-portada.webp', 'canciones/portadas'),
                'descripcion' => $DESCRIPCION_1,
                'letra' => $LETRA_1,
            ]
        );

        $this->asignarCategorias($cancion1, ['Música Tradicional']);
        $this->crearAudio($cancion1, 'el-mandil-de-carolina-audio-1.mp3', 'El mandil de Carolina', 1);
        $this->crearAudio($cancion1, 'el-mandil-de-carolina-audio-2.mp3', 'El_mandil_de_carolina_gaita', 2);

        $cancion2 = Cancion::updateOrCreate(
            ['slug' => 'donde-vas-adelaida'],
            [
                'titulo' => '¿Dónde vas Adelaida?',
                'artista' => 'Desconocido',
                'album' => 'Desconocido',
                'anio' => 1855,
                'duracion' => null,
                'descripcion' => $DESCRIPCION_2,
                'letra' => $LETRA_2,
            ]
        );

        $this->asignarCategorias($cancion2, ['Música Tradicional']);

    }

    /**
     * @param  array<int, string>  $nombresCategorias
     */
    private function asignarCategorias(Cancion $cancion, array $nombresCategorias): void
    {
        $ids = Categoria::where('grupo', 'cancion')
            ->whereIn('nombre', $nombresCategorias)
            ->pluck('id');

        $cancion->categorias()->syncWithoutDetaching($ids);
    }

    private function crearAudio(Cancion $cancion, string $nombreArchivo, string $titulo, int $orden): void
    {
        $ruta = $this->copiarAFichero($nombreArchivo, 'canciones/audios');

        AudioCancion::updateOrCreate(
            ['cancion_id' => $cancion->id, 'archivo' => $ruta],
            ['titulo' => $titulo, 'orden' => $orden]
        );
    }

    private function copiarAFichero(string $nombreArchivo, string $carpetaDestino): string
    {
        $rutaDestino = $carpetaDestino.'/'.$nombreArchivo;

        if (! Storage::disk('public')->exists($rutaDestino)) {
            Storage::disk('public')->put($rutaDestino, file_get_contents(self::RUTA_DATOS.'/'.$nombreArchivo));
        }

        return $rutaDestino;
    }
}
