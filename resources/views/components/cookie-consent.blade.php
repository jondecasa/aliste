<div
    x-data="{
        mostrar: false,
        init() {
            this.mostrar = !localStorage.getItem('aliste_cookies_respuesta');
        },
        aceptar() {
            localStorage.setItem('aliste_cookies_respuesta', 'aceptadas');
            this.mostrar = false;
        },
        rechazar() {
            localStorage.setItem('aliste_cookies_respuesta', 'rechazadas');
            this.mostrar = false;
        },
    }"
    x-show="mostrar"
    x-cloak
    class="fixed inset-x-0 bottom-0 z-50 p-4 sm:p-6"
>
    <div class="max-w-3xl mx-auto bg-tinta text-white rounded-2xl shadow-[0_8px_24px_rgba(0,0,0,0.25)] p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <p class="text-sm text-white/85 leading-relaxed flex-1">
            Utilizamos cookies propias y de terceros para el correcto funcionamiento de la web y, si nos das tu consentimiento, para analizar la navegación.
            Puedes aceptarlas, rechazar las que no sean esenciales o consultar más información en nuestra
            <a href="{{ route('cookies') }}" wire:navigate class="underline hover:text-white">política de cookies</a>.
        </p>

        <div class="flex gap-3 flex-shrink-0 w-full sm:w-auto">
            <button
                x-on:click="rechazar()"
                class="flex-1 sm:flex-none border border-white/40 text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-white/10 transition"
            >
                Rechazar
            </button>
            <button
                x-on:click="aceptar()"
                class="flex-1 sm:flex-none bg-terracota text-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-terracota-dark transition"
            >
                Aceptar
            </button>
        </div>
    </div>
</div>
