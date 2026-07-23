<?php

use App\Models\Banner;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Pueblo;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public function mount(): void
    {
        View::share('ogDescripcion', 'La web de la comarca de Aliste (Zamora): noticias, pueblos, servicios y eventos de la zona.');
    }

    public function with(): array
    {
        return [
            'banner' => Banner::obtener(),
            'pueblosDestacados' => Pueblo::withCount('servicios')
                ->orderByDesc('servicios_count')
                ->orderBy('nombre')
                ->take(3)
                ->get(),
            'categoriasDestacadas' => Categoria::deGrupo('servicio')
                ->withCount('servicios')
                ->orderByDesc('servicios_count')
                ->take(3)
                ->get(),
            'ultimasNoticias' => Noticia::orderByDesc('publicado_en')
                ->take(3)
                ->get(),
            'proximosEventos' => Evento::with(['pueblo', 'categoria'])
                ->where('es_principal', true)
                ->whereBetween('fecha_inicio', [now()->startOfDay(), now()->addDay()->endOfDay()])
                ->orderBy('fecha_inicio')
                ->get(),
            'eventosCalendario' => Evento::with(['pueblo', 'categoria'])
                ->where('es_principal', true)
                ->get()
                ->map(fn (Evento $evento) => [
                    'title' => $evento->titulo.' · '.$evento->pueblo->nombre,
                    'start' => $evento->fecha_inicio->toIso8601String(),
                    'end' => $evento->fecha_fin?->toIso8601String(),
                    'color' => $evento->categoria->color ?? '#78716c',
                    'extendedProps' => [
                        'pueblo' => $evento->pueblo->nombre,
                        'lugar' => $evento->lugar,
                        'descripcion' => $evento->descripcion,
                        'imagen' => $evento->imagen_url,
                        'categoria' => $evento->categoria->nombre ?? null,
                    ],
                ]),
        ];
    }
}; ?>

<div>
    <div class="relative h-[280px] sm:h-[460px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-foto-placeholder bg-cover bg-center flex items-end" style="background-position:bottom;background-image: url('{{ asset('images/aliste-home.png') }}')">
        <div class="w-full bg-gradient-to-t from-black/55 to-transparent p-6 sm:p-12">
            <h1 class="font-serif text-2xl sm:text-5xl text-white leading-tight max-w-2xl mb-4 sm:mb-6">
                La vida de nuestros pueblos, contada por nosotros
            </h1>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('pueblos') }}" wire:navigate
                    class="text-center bg-terracota text-white px-6 py-3.5 rounded-full font-bold text-sm sm:text-[15px]">
                    Explorar pueblos
                </a>
                <a href="{{ route('servicios') }}" wire:navigate
                    class="text-center bg-white/15 text-white border border-white/60 px-6 py-3.5 rounded-full font-bold text-sm sm:text-[15px]">
                    Ver servicios
                </a>
            </div>
        </div>
    </div>

    @if ($banner->contenido_html)
        <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-10 sm:pt-16">
            {!! $banner->contenido_html !!}
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-10 sm:pt-16" x-data="{ eventoSeleccionado: null }">
        <h2 class="font-serif text-xl sm:text-[28px] text-tinta mb-5 sm:mb-7">Calendario de la comarca</h2>

        <div
            wire:ignore
            x-init="
                const calendario = new FullCalendar.Calendar($el, {
                    plugins: [FullCalendar.dayGridPlugin, FullCalendar.listPlugin, FullCalendar.interactionPlugin],
                    initialView: 'dayGridMonth',
                    locale: FullCalendar.esLocale,
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,listMonth',
                    },
                    events: @js($eventosCalendario),
                    eventClick: (info) => {
                        eventoSeleccionado = {
                            titulo: info.event.title,
                            color: info.event.backgroundColor,
                            pueblo: info.event.extendedProps.pueblo,
                            lugar: info.event.extendedProps.lugar,
                            descripcion: info.event.extendedProps.descripcion,
                            imagen: info.event.extendedProps.imagen,
                            categoria: info.event.extendedProps.categoria,
                            fecha: info.event.start.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
                        };
                    },
                });
                calendario.render();
            "
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-6 shadow-[0_8px_24px_rgba(60,30,10,0.08)]"
        ></div>

        <div
            x-show="eventoSeleccionado"
            x-cloak
            class="mt-6 bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)] flex flex-col sm:flex-row"
        >
            <template x-if="eventoSeleccionado?.imagen">
                <div class="sm:w-56 flex-shrink-0 aspect-[16/9] sm:aspect-auto bg-foto-placeholder">
                    <img :src="eventoSeleccionado.imagen" class="w-full h-full object-cover">
                </div>
            </template>

            <div class="p-6 flex-1">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold uppercase" :style="{ color: eventoSeleccionado?.color ?? '#78716c' }" x-text="eventoSeleccionado?.categoria ?? 'Evento'"></div>
                    <button @click="eventoSeleccionado = null" class="text-tinta-muted hover:text-tinta text-sm">✕</button>
                </div>
                <div class="font-serif font-semibold text-xl text-tinta mt-1" x-text="eventoSeleccionado?.titulo"></div>
                <div class="text-sm text-tinta-muted mt-1" x-text="eventoSeleccionado?.pueblo"></div>
                <div class="text-sm text-tinta-muted" x-text="eventoSeleccionado?.fecha"></div>
                <template x-if="eventoSeleccionado?.lugar">
                    <div class="text-sm text-tinta-muted mt-1" x-text="eventoSeleccionado.lugar"></div>
                </template>
                <template x-if="eventoSeleccionado?.descripcion">
                    <p class="text-[15px] text-tinta-muted mt-3 leading-relaxed" x-text="eventoSeleccionado.descripcion"></p>
                </template>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-8 flex flex-col lg:flex-row gap-8 py-10 sm:py-16">
        <div class="flex-1 min-w-0 order-2 lg:order-none">
            <h2 class="font-serif text-xl sm:text-[28px] text-tinta mb-5 sm:mb-7">Pueblos destacados</h2>

            <div class="flex sm:grid sm:grid-cols-3 gap-4 sm:gap-6 overflow-x-auto sm:overflow-visible pb-2 sm:pb-0 mb-10 sm:mb-12">
                @foreach ($pueblosDestacados as $pueblo)
                    <a href="{{ route('pueblo', $pueblo) }}" wire:navigate class="w-40 sm:w-auto flex-shrink-0 bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                        <div class="aspect-[4/3] bg-foto-placeholder flex items-center justify-center text-tinta-muted text-[11px]">
                            @if ($pueblo->portada_url)
                                <img src="{{ $pueblo->portada_url }}" alt="{{ $pueblo->nombre }}" class="w-full h-full object-cover">
                            @else
                                foto pueblo
                            @endif
                        </div>
                        <div class="p-4">
                            <div class="font-serif font-semibold text-sm sm:text-base text-tinta">{{ $pueblo->nombre }}</div>
                            <div class="text-xs text-tinta-muted mt-1">{{ $pueblo->servicios_count }} servicios</div>
                        </div>
                    </a>
                @endforeach
            </div>

            <h2 class="font-serif text-xl sm:text-[28px] text-tinta mb-5 sm:mb-7">Servicios y negocios locales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                @foreach ($categoriasDestacadas as $categoria)
                    <a href="{{ route('servicios') }}" wire:navigate class="bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-7 shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                        <div class="font-serif italic font-semibold text-base sm:text-lg text-tinta">{{ $categoria->nombre }}</div>
                        <div class="text-sm text-tinta-muted mt-2">{{ $categoria->servicios_count }} {{ Str::plural('negocio', $categoria->servicios_count) }} registrados</div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="w-full lg:w-[300px] flex-shrink-0 flex flex-col gap-6 order-1 lg:order-none">
            @if ($proximosEventos->isNotEmpty())
                <div class="rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                    <div class="bg-terracota text-white px-5 py-4 font-serif font-semibold text-lg">Próximos eventos</div>
                    <div class="bg-white dark:bg-gray-800 p-5 flex flex-col gap-4">
                        @foreach ($proximosEventos as $evento)
                            <a href="{{ route('pueblo.calendario', $evento->pueblo) }}" wire:navigate wire:key="evento-home-{{ $evento->id }}" class="block">
                                <div class="flex items-center gap-1.5 text-xs text-terracota font-bold uppercase">
                                    @if ($evento->categoria?->color)
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $evento->categoria->color }}"></span>
                                    @endif
                                    {{ $evento->pueblo->nombre }} ({{ $evento->fecha_inicio->isToday() ? 'hoy' : 'mañana' }})
                                </div>
                                <div class="font-serif font-semibold text-sm text-tinta mt-0.5">
                                    {{ $evento->titulo }}
                                    <span class="font-sans font-normal text-tinta-muted">
                                        | {{ $evento->fecha_inicio->format('H:i') }}{{ $evento->fecha_fin ? '-'.$evento->fecha_fin->format('H:i') : '' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                <div class="bg-tinta text-white px-5 py-4 font-serif font-semibold text-lg">Últimas noticias</div>
                <div class="bg-white dark:bg-gray-800 p-5 flex flex-col gap-4">
                    @forelse ($ultimasNoticias as $noticia)
                        <a href="{{ route('noticia', $noticia) }}" wire:navigate class="block">
                            <div class="text-xs text-tinta-muted/80">{{ $noticia->publicado_en?->translatedFormat('j \d\e F Y') }}</div>
                            <div class="font-serif font-semibold text-sm text-tinta mt-0.5">{{ $noticia->titulo }}</div>
                        </a>
                    @empty
                        <p class="text-sm text-tinta-muted">Todavía no hay noticias publicadas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
