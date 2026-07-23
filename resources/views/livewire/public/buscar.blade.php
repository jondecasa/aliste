<?php

use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Pueblo;
use App\Models\Servicio;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    #[Url]
    public string $q = '';

    public function mount(): void
    {
        View::share('title', $this->q !== '' ? "Buscar: {$this->q} · Aliste.es" : 'Buscar · Aliste.es');
        View::share('ogDescripcion', 'Busca pueblos, servicios, noticias y eventos de la comarca de Aliste.');
    }

    public function with(): array
    {
        $q = trim($this->q);

        if ($q === '') {
            return [
                'q' => $q,
                'pueblos' => collect(),
                'servicios' => collect(),
                'noticias' => collect(),
                'eventos' => collect(),
                'total' => 0,
            ];
        }

        $pueblos = Pueblo::where('nombre', 'like', "%{$q}%")
            ->orderBy('nombre')
            ->take(8)
            ->get();

        $servicios = Servicio::query()
            ->with(['pueblo', 'categorias'])
            ->where(function ($sq) use ($q) {
                $sq->where('nombre', 'like', "%{$q}%")
                    ->orWhereHas('pueblo', fn ($pq) => $pq->where('nombre', 'like', "%{$q}%"))
                    ->orWhereHas('categorias', fn ($cq) => $cq->where('categorias.nombre', 'like', "%{$q}%"));
            })
            ->orderBy('nombre')
            ->take(8)
            ->get();

        $noticias = Noticia::query()
            ->with('pueblo')
            ->where(function ($nq) use ($q) {
                $nq->where('titulo', 'like', "%{$q}%")
                    ->orWhere('extracto', 'like', "%{$q}%");
            })
            ->orderByDesc('publicado_en')
            ->take(8)
            ->get();

        $eventos = Evento::query()
            ->with('pueblo')
            ->where(function ($eq) use ($q) {
                $eq->where('titulo', 'like', "%{$q}%")
                    ->orWhereHas('pueblo', fn ($pq) => $pq->where('nombre', 'like', "%{$q}%"));
            })
            ->orderByDesc('fecha_inicio')
            ->take(8)
            ->get();

        return [
            'q' => $q,
            'pueblos' => $pueblos,
            'servicios' => $servicios,
            'noticias' => $noticias,
            'eventos' => $eventos,
            'total' => $pueblos->count() + $servicios->count() + $noticias->count() + $eventos->count(),
        ];
    }
}; ?>

<div>
    <div class="relative h-[140px] sm:h-[180px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-tinta flex items-end">
        <div class="relative w-full p-6 sm:p-10">
            <h1 class="font-serif text-2xl sm:text-4xl text-white">
                @if ($q !== '')
                    Resultados para "{{ $q }}"
                @else
                    Buscar en la comarca
                @endif
            </h1>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-8 py-10 sm:py-14">
        <form action="{{ route('buscar') }}" method="GET" class="mb-10">
            <div class="flex items-center gap-2 h-12 rounded-full border border-tinta-borde bg-white px-5">
                <svg class="w-4 h-4 text-tinta-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                </svg>
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Buscar pueblos, servicios, noticias, eventos..."
                    class="flex-1 border-none bg-transparent p-0 text-sm text-tinta placeholder:text-tinta-muted focus:ring-0"
                    autofocus
                >
            </div>
        </form>

        @if ($q === '')
            <p class="text-sm text-tinta-muted">Escribe algo para buscar en toda la comarca.</p>
        @elseif ($total === 0)
            <p class="text-sm text-tinta-muted">No hemos encontrado nada para "{{ $q }}". Prueba con otra palabra.</p>
        @else
            <div class="space-y-12">
                @if ($pueblos->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-xl text-tinta mb-4">Pueblos</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($pueblos as $pueblo)
                                <a href="{{ route('pueblo', $pueblo) }}" wire:navigate
                                    class="bg-white rounded-xl p-4 shadow-[0_4px_16px_rgba(60,30,10,0.06)] hover:shadow-[0_8px_24px_rgba(60,30,10,0.1)] transition">
                                    <div class="font-serif text-tinta">{{ $pueblo->nombre }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($servicios->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-xl text-tinta mb-4">Servicios</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($servicios as $servicio)
                                <div class="bg-white rounded-xl p-4 shadow-[0_4px_16px_rgba(60,30,10,0.06)]">
                                    <div class="text-xs text-terracota font-semibold uppercase">
                                        {{ $servicio->categorias->pluck('nombre')->join(', ') }}
                                        @if ($servicio->pueblo) · {{ $servicio->pueblo->nombre }} @endif
                                    </div>
                                    <div class="font-serif text-tinta mt-1">{{ $servicio->nombre }}</div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('servicios') }}" wire:navigate class="inline-block mt-3 text-sm text-terracota font-semibold">
                            Ver todos los servicios →
                        </a>
                    </div>
                @endif

                @if ($noticias->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-xl text-tinta mb-4">Noticias</h2>
                        <div class="space-y-3">
                            @foreach ($noticias as $noticia)
                                <a href="{{ route('noticia', $noticia) }}" wire:navigate
                                    class="block bg-white rounded-xl p-4 shadow-[0_4px_16px_rgba(60,30,10,0.06)] hover:shadow-[0_8px_24px_rgba(60,30,10,0.1)] transition">
                                    <div class="text-xs text-tinta-muted">
                                        {{ $noticia->publicado_en?->translatedFormat('j \d\e F Y') }}
                                        @if ($noticia->pueblo) · {{ $noticia->pueblo->nombre }} @endif
                                    </div>
                                    <div class="font-serif text-tinta mt-1">{{ $noticia->titulo }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($eventos->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-xl text-tinta mb-4">Eventos</h2>
                        <div class="space-y-3">
                            @foreach ($eventos as $evento)
                                <a href="{{ $evento->pueblo ? route('pueblo.calendario', $evento->pueblo) : route('inicio') }}" wire:navigate
                                    class="block bg-white rounded-xl p-4 shadow-[0_4px_16px_rgba(60,30,10,0.06)] hover:shadow-[0_8px_24px_rgba(60,30,10,0.1)] transition">
                                    <div class="text-xs text-tinta-muted">
                                        {{ $evento->fecha_inicio?->translatedFormat('j \d\e F Y') }}
                                        @if ($evento->pueblo) · {{ $evento->pueblo->nombre }} @endif
                                    </div>
                                    <div class="font-serif text-tinta mt-1">{{ $evento->titulo }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
