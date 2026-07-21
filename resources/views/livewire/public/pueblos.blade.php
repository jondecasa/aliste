<?php

use App\Models\Pueblo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public const ALOJAMIENTO_SLUGS = ['alojamiento', 'casa-rural', 'hotel', 'camping'];

    public string $buscar = '';
    public string $filtro = 'todos';
    public int $porPagina = 12;

    public function verMas(): void
    {
        $this->porPagina += 12;
    }

    public function updated($property): void
    {
        if (in_array($property, ['buscar', 'filtro'])) {
            $this->porPagina = 12;
        }
    }

    public function with(): array
    {
        $query = Pueblo::query()
            ->withCount(['servicios', 'puntosInteres'])
            ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
            ->when($this->filtro === 'alojamiento', fn ($q) => $q->whereHas(
                'servicios.categorias',
                fn ($sq) => $sq->whereIn('categorias.slug', self::ALOJAMIENTO_SLUGS)
            ))
            ->when($this->filtro === 'poi', fn ($q) => $q->has('puntosInteres'))
            ->orderBy('nombre');

        return [
            'total' => $query->count(),
            'pueblos' => (clone $query)->take($this->porPagina)->get(),
        ];
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-8 sm:pt-12 pb-6 sm:pb-8">
        <h1 class="font-serif text-3xl sm:text-[38px] text-tinta mb-3">Pueblos de la comarca</h1>
        <p class="text-sm sm:text-[15px] text-tinta-muted max-w-xl leading-relaxed mb-6">
            {{ $total }} {{ Str::plural('pueblo', $total) }}, cada uno con sus servicios, rutas y vida propia. Encuentra el tuyo.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
            <input
                wire:model.live.debounce.300ms="buscar"
                type="text"
                placeholder="Buscar un pueblo..."
                class="w-full sm:max-w-[340px] h-11 rounded-full border border-tinta-borde bg-white px-5 text-sm text-tinta placeholder:text-tinta-muted focus:outline-none focus:ring-2 focus:ring-terracota/40"
            >
            <div class="flex gap-2 flex-wrap">
                <button wire:click="$set('filtro', 'todos')"
                    class="px-4 py-2.5 rounded-full text-[13px] {{ $filtro === 'todos' ? 'bg-terracota text-white font-semibold' : 'border border-tinta-borde text-tinta/70' }}">
                    Todos
                </button>
                <button wire:click="$set('filtro', 'alojamiento')"
                    class="px-4 py-2.5 rounded-full text-[13px] {{ $filtro === 'alojamiento' ? 'bg-terracota text-white font-semibold' : 'border border-tinta-borde text-tinta/70' }}">
                    Con alojamiento
                </button>
                <button wire:click="$set('filtro', 'poi')"
                    class="px-4 py-2.5 rounded-full text-[13px] {{ $filtro === 'poi' ? 'bg-terracota text-white font-semibold' : 'border border-tinta-borde text-tinta/70' }}">
                    Con puntos de interés
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-8 pb-14 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        @forelse ($pueblos as $pueblo)
            <a href="{{ route('pueblo', $pueblo) }}" wire:navigate wire:key="pueblo-{{ $pueblo->id }}" class="block bg-white rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                <div class="aspect-[4/3] bg-foto-placeholder flex items-center justify-center text-tinta-muted text-[11px]">
                    @if ($pueblo->portada_url)
                        <img src="{{ $pueblo->portada_url }}" alt="{{ $pueblo->nombre }}" class="w-full h-full object-cover">
                    @else
                        foto pueblo
                    @endif
                </div>
                <div class="p-4">
                    <div class="font-serif font-semibold text-sm sm:text-base text-tinta">{{ $pueblo->nombre }}</div>
                    <div class="text-xs text-tinta-muted mt-1">
                        {{ $pueblo->servicios_count }} {{ Str::plural('servicio', $pueblo->servicios_count) }}
                        @if ($pueblo->puntos_interes_count)
                            · {{ $pueblo->puntos_interes_count }} {{ Str::plural('punto de interés', $pueblo->puntos_interes_count) }}
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <p class="col-span-full text-center text-tinta-muted py-10">No se han encontrado pueblos.</p>
        @endforelse
    </div>

    @if ($total > count($pueblos))
        <div class="flex justify-center pb-14">
            <button wire:click="verMas" class="bg-white border-[1.5px] border-terracota text-terracota px-7 py-3 rounded-full font-bold text-sm">
                Cargar más pueblos
            </button>
        </div>
    @endif
</div>
