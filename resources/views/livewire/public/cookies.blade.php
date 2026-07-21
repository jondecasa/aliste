<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    //
}; ?>

<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-8 pt-8 sm:pt-12 pb-14">
        <h1 class="font-serif text-3xl sm:text-[38px] text-tinta mb-8">Política de cookies</h1>

        <div class="prose prose-neutral max-w-none">
            <p>
                Cookie es un fichero que se descarga en su ordenador al acceder a determinadas páginas web. Las cookies
                permiten a una página web, entre otras cosas, almacenar y recuperar información sobre los hábitos de
                navegación de un usuario o de su equipo y, dependiendo de la información que contengan y de la forma en
                que utilice su equipo, pueden utilizarse para reconocer al usuario. El navegador del usuario memoriza
                cookies en el disco duro solamente durante la sesión actual ocupando un espacio de memoria mínimo y no
                perjudicando al ordenador. Las cookies no contienen ninguna clase de información personal específica, y
                la mayoría de las mismas se borran del disco duro al finalizar la sesión de navegador (las denominadas
                cookies de sesión).
            </p>

            <p>
                La mayoría de los navegadores aceptan como estándar a las cookies y, con independencia de las mismas,
                permiten o impiden en los ajustes de seguridad las cookies temporales o memorizadas.
            </p>

            <p>
                Sin su expreso consentimiento —mediante la activación de las cookies en su navegador— no enlazará en
                las cookies los datos memorizados con sus datos personales proporcionados en el momento del registro o
                la compra.
            </p>

            <h2>Amazon Afiliados</h2>
            <p>
                Si encuentra enlaces de Amazon en nuestra página web, serán enlaces del programa de Amazon Afiliados.
                Una vez pulse en ellos, Amazon reconoce que viene de visitar una de nuestras páginas. Más información en
                <a href="https://www.amazon.es/gp/help/customer/display.html/?nodeId=200545460" target="_blank" rel="noopener">Amazon</a>.
            </p>

            <h2>¿Qué tipos de cookies utiliza esta página web?</h2>

            <ul>
                <li>
                    <strong>Cookies técnicas:</strong> son aquéllas que permiten al usuario la navegación a través de
                    una página web, plataforma o aplicación y la utilización de las diferentes opciones o servicios que
                    en ella existan como, por ejemplo, controlar el tráfico y la comunicación de datos, identificar la
                    sesión, acceder a partes de acceso restringido, recordar los elementos que integran un pedido,
                    realizar el proceso de compra de un pedido, realizar la solicitud de inscripción o participación en
                    un evento, utilizar elementos de seguridad durante la navegación, almacenar contenidos para la
                    difusión de vídeos o sonido o compartir contenidos a través de redes sociales.
                </li>
                <li>
                    <strong>Cookies de personalización:</strong> son aquéllas que permiten al usuario acceder al
                    servicio con algunas características de carácter general predefinidas en función de una serie de
                    criterios en el terminal del usuario como por ejemplo serían el idioma, el tipo de navegador a
                    través del cual accede al servicio, la configuración regional desde donde accede al servicio, etc.
                </li>
                <li>
                    <strong>Cookies de análisis:</strong> son aquéllas que, bien tratadas por nosotros o por terceros,
                    nos permiten cuantificar el número de usuarios y así realizar la medición y análisis estadístico de
                    la utilización que hacen los usuarios del servicio ofertado. Para ello se analiza su navegación en
                    nuestra página web con el fin de mejorar la oferta de productos o servicios que le ofrecemos.
                </li>
                <li>
                    <strong>Cookies de terceros:</strong> la web puede utilizar servicios de terceros (Google
                    Analytics) que, por cuenta de Google, recopilarán información con fines estadísticos, de uso del
                    site por parte del usuario y para la prestación de otros servicios relacionados con la actividad
                    del website y otros servicios de Internet. En particular, este sitio web utiliza Google Analytics,
                    un servicio analítico de web prestado por Google, Inc. con domicilio en los Estados Unidos con sede
                    central en 1600 Amphitheatre Parkway, Mountain View, California 94043. Para la prestación de estos
                    servicios, estos utilizan cookies que recopilan la información, incluida la dirección IP del
                    usuario, que será transmitida, tratada y almacenada por Google en los términos fijados en la web
                    Google.com. Incluyendo la posible transmisión de dicha información a terceros por razones de
                    exigencia legal o cuando dichos terceros procesen la información por cuenta de Google.
                </li>
            </ul>

            <p>
                El usuario acepta expresamente, por la utilización de este site, el tratamiento de la información
                recabada en la forma y con los fines anteriormente mencionados. Y asimismo reconoce conocer la
                posibilidad de rechazar el tratamiento de tales datos o información rechazando el uso de cookies
                mediante la selección de la configuración apropiada a tal fin en su navegador. Si bien esta opción de
                bloqueo de cookies en su navegador puede no permitirle el uso pleno de todas las funcionalidades del
                website.
            </p>

            <p>
                Puede usted permitir, bloquear o eliminar las cookies instaladas en su equipo mediante la
                configuración de las opciones del navegador instalado en su ordenador:
            </p>

            <ul>
                <li><a href="http://support.google.com/chrome/bin/answer.py?hl=es&answer=95647" target="_blank" rel="noopener">Chrome</a></li>
                <li><a href="http://windows.microsoft.com/es-es/windows7/how-to-manage-cookies-in-internet-explorer-9" target="_blank" rel="noopener">Explorer</a></li>
                <li><a href="http://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-que-los-sitios-we" target="_blank" rel="noopener">Firefox</a></li>
                <li><a href="http://support.apple.com/kb/ph5042" target="_blank" rel="noopener">Safari</a></li>
            </ul>

            <p>
                Si tiene dudas sobre esta política de cookies, puede
                <a href="{{ route('contacto') }}" wire:navigate>contactar con nosotros</a>.
            </p>
        </div>
    </div>
</div>
