<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    //
}; ?>

<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-8 pt-8 sm:pt-12 pb-14">
        <h1 class="font-serif text-3xl sm:text-[38px] text-tinta mb-8">Política de privacidad</h1>

        <div class="prose prose-neutral max-w-none">
            <p>
                En Aliste.es respetamos tu privacidad y solo recopilamos los datos personales necesarios para el
                funcionamiento del sitio. Esta página explica qué información tratamos, con qué finalidad y qué
                derechos tienes al respecto.
            </p>

            <h2>¿Qué datos recopilamos?</h2>
            <ul>
                <li>
                    <strong>Al crear una cuenta o registrarte:</strong> tu nombre, correo electrónico y contraseña
                    (almacenada de forma cifrada). Opcionalmente, el pueblo con el que te asocias y una foto de
                    perfil.
                </li>
                <li>
                    <strong>Al iniciar sesión con Google:</strong> tu nombre, correo electrónico e identificador de
                    cuenta de Google, que Google nos proporciona con tu autorización expresa.
                </li>
                <li>
                    <strong>Al usar el formulario de contacto:</strong> el nombre, correo electrónico, asunto y
                    mensaje que nos escribas. Este formulario está protegido con reCAPTCHA de Google para evitar
                    envíos automatizados.
                </li>
                <li>
                    <strong>Si activas las notificaciones push:</strong> tu navegador genera una suscripción técnica
                    (sin datos personales adicionales) que asociamos a tu cuenta para poder enviarte avisos.
                </li>
            </ul>

            <h2>¿Para qué usamos tus datos?</h2>
            <ul>
                <li>Gestionar tu cuenta y permitirte iniciar sesión.</li>
                <li>Mostrarte contenido relacionado con tu pueblo, si lo indicas.</li>
                <li>Enviarte notificaciones push, únicamente si las has activado tú mismo.</li>
                <li>Responder a tus consultas a través del formulario de contacto.</li>
            </ul>

            <h2>¿Con quién compartimos tus datos?</h2>
            <p>
                No vendemos ni cedemos tus datos personales a terceros con fines comerciales. Solo compartimos
                información con los proveedores estrictamente necesarios para el funcionamiento técnico del sitio:
            </p>
            <ul>
                <li><strong>Google</strong>, si eliges iniciar sesión con tu cuenta de Google o al usar el reCAPTCHA del formulario de contacto.</li>
                <li>El proveedor de alojamiento (hosting) donde se ejecuta la aplicación.</li>
            </ul>

            <h2>¿Cuánto tiempo conservamos tus datos?</h2>
            <p>
                Mientras tu cuenta permanezca activa. Si quieres que eliminemos tu cuenta y tus datos, puedes
                solicitarlo en cualquier momento a través del formulario de contacto.
            </p>

            <h2>Tus derechos</h2>
            <p>
                Puedes ejercer en cualquier momento tus derechos de acceso, rectificación, supresión, oposición,
                limitación del tratamiento y portabilidad de tus datos, escribiéndonos a través de la
                <a href="{{ route('contacto') }}" wire:navigate>página de contacto</a>.
            </p>

            <h2>Seguridad</h2>
            <p>
                El sitio utiliza conexión cifrada (HTTPS) y las contraseñas se almacenan siempre de forma cifrada,
                nunca en texto plano.
            </p>

            <h2>Menores de edad</h2>
            <p>
                Aliste.es no está dirigida a menores de 14 años. Si detectamos que se ha registrado un menor sin
                autorización de sus padres o tutores, procederemos a eliminar su cuenta.
            </p>

            <h2>Cookies</h2>
            <p>
                El uso de cookies en el sitio se explica en nuestra
                <a href="{{ route('cookies') }}" wire:navigate>política de cookies</a>.
            </p>

            <h2>Cambios en esta política</h2>
            <p>
                Podemos actualizar esta política de privacidad para adaptarla a cambios legislativos o del propio
                servicio. Te recomendamos revisarla periódicamente.
            </p>

            <p>
                Si tienes cualquier duda sobre esta política de privacidad, puedes
                <a href="{{ route('contacto') }}" wire:navigate>contactar con nosotros</a>.
            </p>
        </div>
    </div>
</div>
