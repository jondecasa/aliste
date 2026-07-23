<?php

use App\Models\Noticia;
use App\Models\Pueblo;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Pueblo $pueblo;

    public function mount(Pueblo $pueblo): void
    {
        $this->pueblo = $pueblo;

        View::share('title', $this->pueblo->nombre.' · Aliste.es');
        View::share('ogDescripcion', $this->pueblo->descripcion
            ? Str::limit(strip_tags($this->pueblo->descripcion), 200)
            : "Descubre {$this->pueblo->nombre}, un pueblo de la comarca de Aliste.");
        View::share('ogImagen', $this->pueblo->portada_url);
        View::share('ogUrl', route('pueblo', $this->pueblo));
    }

    public function with(): array
    {
        return [
            'noticias' => Noticia::where('pueblo_id', $this->pueblo->id)
                ->orderByDesc('publicado_en')
                ->take(6)
                ->get(),
            'servicios' => $this->pueblo->servicios()
                ->with('categorias')
                ->orderBy('nombre')
                ->get(),
            'puntosInteresMapa' => $this->pueblo->puntosInteres()
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->get()
                ->map(fn ($punto) => [
                    'nombre' => $punto->nombre,
                    'latitud' => (float) $punto->latitud,
                    'longitud' => (float) $punto->longitud,
                    'foto' => $punto->foto_url,
                ]),
        ];
    }
}; ?>

<div>
    <div class="relative h-[220px] sm:h-[340px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-foto-placeholder flex items-end">
        @if ($pueblo->portada_url)
            <img src="{{ $pueblo->portada_url }}" alt="{{ $pueblo->nombre }}" class="absolute inset-0 w-full h-full object-cover">
        @endif
        <div class="relative w-full bg-gradient-to-t from-black/60 to-transparent p-6 sm:p-10">
            <a href="{{ route('pueblos') }}" wire:navigate class="text-white/80 text-xs mb-2 inline-block">← Volver a pueblos</a>
            <h1 class="font-serif text-2xl sm:text-4xl text-white">{{ $pueblo->nombre }}</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-10 sm:py-14 flex flex-col lg:flex-row gap-8">
        <div class="flex-1 min-w-0 max-w-3xl">
            @if ($pueblo->poblacion || $pueblo->altitud)
                <div class="grid grid-cols-2 gap-4 mb-6">
                    @if ($pueblo->poblacion)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                            <div class="text-2xl sm:text-3xl font-serif font-semibold text-terracota">{{ $pueblo->poblacion }}</div>
                            <div class="text-xs text-tinta-muted mt-1">Habitantes</div>
                        </div>
                    @endif

                    @if ($pueblo->altitud)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                            <div class="text-2xl sm:text-3xl font-serif font-semibold text-terracota">{{ $pueblo->altitud }} <span class="text-base font-sans font-normal text-tinta-muted">m</span></div>
                            <div class="text-xs text-tinta-muted mt-1">Altitud</div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($pueblo->latitud && $pueblo->longitud)
                <div
                    wire:ignore
                    x-data
                    x-init="
                        const mapa = L.map($el).setView([{{ $pueblo->latitud }}, {{ $pueblo->longitud }}], 15);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href=&quot;https://www.openstreetmap.org/copyright&quot;>OpenStreetMap</a>',
                            maxZoom: 19,
                        }).addTo(mapa);

                        const popupHtml = (nombre, foto) => {
                            const titulo = '<div style=&quot;font-weight:600;margin-top:' + (foto ? '6px' : '0') + ';&quot;>' + nombre + '</div>';
                            const img = foto ? ('<img src=&quot;' + foto + '&quot; style=&quot;width:160px;height:110px;object-fit:cover;border-radius:8px;display:block;&quot;>') : '';
                            return img + titulo;
                        };

                        const limites = [];

                        @js($puntosInteresMapa).forEach((punto) => {
                            L.marker([punto.latitud, punto.longitud])
                                .addTo(mapa)
                                .bindPopup(popupHtml(punto.nombre, punto.foto));
                            limites.push([punto.latitud, punto.longitud]);
                        });

                        if (limites.length > 0) {
                            mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 16 });
                        }
                    "
                    class="w-full h-[420px] sm:h-[480px] rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)] mb-8"
                ></div>
            @endif

            @if ($pueblo->descripcion)
                <p class="text-tinta-muted text-[15px] leading-relaxed mb-8">{{ $pueblo->descripcion }}</p>
            @endif

            @if ($pueblo->contenido_html)
                <div class="prose prose-neutral dark:prose-invert max-w-none">
                    {!! $pueblo->contenido_html !!}
                </div>
            @else
                <p class="text-tinta-muted text-sm italic">Todavía no hay contenido para este pueblo.</p>
            @endif
        </div>

        <div class="w-full lg:w-[300px] flex-shrink-0 flex flex-col gap-6">
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('pueblo.calendario', $pueblo) }}" wire:navigate
                    class="flex items-center justify-center gap-2 bg-white dark:bg-gray-800 rounded-2xl py-4 px-3 shadow-[0_8px_24px_rgba(60,30,10,0.08)] font-serif font-semibold text-sm text-tinta hover:text-terracota">
                    Calendario
                </a>
                <a href="{{ route('pueblo.gente', $pueblo) }}" wire:navigate
                    class="flex items-center justify-center gap-2 bg-white dark:bg-gray-800 rounded-2xl py-4 px-3 shadow-[0_8px_24px_rgba(60,30,10,0.08)] font-serif font-semibold text-sm text-tinta hover:text-terracota">
                    Gente
                </a>
            </div>

            @if ($noticias->isNotEmpty())
                <div class="rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                    <div class="bg-tinta text-white px-5 py-4 font-serif font-semibold text-lg">Últimas noticias</div>
                    <div class="bg-white dark:bg-gray-800 p-5 flex flex-col gap-4">
                        @foreach ($noticias as $noticia)
                            <a href="{{ route('noticia', $noticia) }}" wire:navigate wire:key="noticia-{{ $noticia->id }}" class="block">
                                <div class="text-xs text-tinta-muted/80">{{ $noticia->publicado_en?->translatedFormat('j \d\e F Y') }}</div>
                                <div class="font-serif font-semibold text-sm text-tinta mt-0.5">{{ $noticia->titulo }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($servicios->isNotEmpty())
                <div class="rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                    <div class="bg-terracota text-white px-5 py-4 font-serif font-semibold text-lg">Servicios</div>
                    <div class="bg-white dark:bg-gray-800 p-5 flex flex-col gap-4">
                        @foreach ($servicios as $servicio)
                            <a href="{{ route('servicios') }}" wire:navigate wire:key="servicio-{{ $servicio->id }}" class="block">
                                @if ($servicio->categorias->isNotEmpty())
                                    <div class="text-xs text-terracota font-bold uppercase">{{ $servicio->categorias->pluck('nombre')->join(' · ') }}</div>
                                @endif
                                <div class="font-serif font-semibold text-sm text-tinta mt-0.5">{{ $servicio->nombre }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
