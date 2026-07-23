<?php

use App\Models\Cancion;
use App\Models\Categoria;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public string $buscar = '';

    #[Url]
    public ?int $categoriaId = null;
    public int $porPagina = 12;

    public function mount(): void
    {
        View::share('title', 'Música · Aliste.es');
        View::share('ogDescripcion', 'Canciones, tonadas y música tradicional de la comarca de Aliste, en Zamora.');
    }

    public function verMas(): void
    {
        $this->porPagina += 12;
    }

    public function updated($property): void
    {
        if (in_array($property, ['buscar', 'categoriaId'])) {
            $this->porPagina = 12;
        }
    }

    public function with(): array
    {
        $query = Cancion::query()
            ->with(['pueblo', 'categorias'])
            ->when($this->buscar, fn ($q) => $q->where(function ($sq) {
                $sq->where('titulo', 'like', "%{$this->buscar}%")
                    ->orWhere('artista', 'like', "%{$this->buscar}%")
                    ->orWhereHas('pueblo', fn ($pq) => $pq->where('nombre', 'like', "%{$this->buscar}%"));
            }))
            ->when($this->categoriaId, fn ($q) => $q->whereHas(
                'categorias',
                fn ($sq) => $sq->where('categorias.id', $this->categoriaId)
            ))
            ->orderBy('titulo');

        return [
            'total' => $query->count(),
            'canciones' => (clone $query)->take($this->porPagina)->get(),
            'categorias' => Categoria::deGrupo('cancion')
                ->withCount('canciones')
                ->having('canciones_count', '>', 0)
                ->orderByDesc('canciones_count')
                ->take(15)
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-8 sm:pt-12 pb-4">
        <h1 class="font-serif text-3xl sm:text-[38px] text-tinta mb-3">Música de la comarca</h1>
        <p class="text-sm sm:text-[15px] text-tinta-muted max-w-xl leading-relaxed mb-6">
            Canciones, tonadas y grabaciones de tradición oral recogidas en los pueblos de Aliste.
        </p>

        <input
            wire:model.live.debounce.300ms="buscar"
            type="text"
            placeholder="Buscar por título, artista o pueblo..."
            class="w-full sm:max-w-[340px] h-11 rounded-full border border-tinta-borde bg-white dark:bg-gray-800 px-5 text-sm text-tinta placeholder:text-tinta-muted focus:outline-none focus:ring-2 focus:ring-terracota/40"
        >
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-8 pb-14 flex flex-col lg:flex-row gap-6 sm:gap-8">
        <div class="w-full lg:w-[220px] flex-shrink-0 flex flex-col gap-2">
            <button wire:click="$set('categoriaId', null)"
                class="text-left px-4 py-3 rounded-[10px] text-sm {{ ! $categoriaId ? 'bg-terracota text-white font-bold' : 'text-tinta/80' }}">
                Todas
            </button>

            <div class="flex flex-col gap-2 max-h-80 overflow-y-auto pr-1">
                @foreach ($categorias as $categoria)
                    <button wire:click="$set('categoriaId', {{ $categoria->id }})"
                        class="text-left px-4 py-3 rounded-[10px] text-sm flex justify-between gap-2 {{ $categoriaId === $categoria->id ? 'bg-terracota text-white font-bold' : 'text-tinta/80' }}">
                        <span>{{ $categoria->nombre }}</span>
                        <span class="opacity-70">{{ $categoria->canciones_count }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 content-start">
            @forelse ($canciones as $cancion)
                <a href="{{ route('cancion', $cancion) }}" wire:navigate wire:key="cancion-{{ $cancion->id }}"
                    class="block bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                    <div class="aspect-square bg-foto-placeholder flex items-center justify-center text-tinta-muted text-[11px]">
                        @if ($cancion->portada_url)
                            <img src="{{ $cancion->portada_url }}" alt="{{ $cancion->titulo }}" class="w-full h-full object-cover">
                        @else
                            portada
                        @endif
                    </div>
                    <div class="p-5">
                        <div class="text-xs text-terracota font-bold uppercase">
                            {{ $cancion->categorias->pluck('nombre')->join(' · ') }}
                            @if ($cancion->pueblo)
                                · {{ $cancion->pueblo->nombre }}
                            @endif
                        </div>
                        <div class="font-serif font-semibold text-lg text-tinta mt-2">{{ $cancion->titulo }}</div>
                        @if ($cancion->artista)
                            <div class="text-[13px] text-tinta-muted mt-1">{{ $cancion->artista }}</div>
                        @endif
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-tinta-muted py-10">No se han encontrado canciones.</p>
            @endforelse
        </div>
    </div>

    @if ($total > count($canciones))
        <div class="flex justify-center pb-14">
            <button wire:click="verMas" class="bg-white dark:bg-gray-800 border-[1.5px] border-terracota text-terracota px-7 py-3 rounded-full font-bold text-sm">
                Cargar más canciones
            </button>
        </div>
    @endif
</div>
