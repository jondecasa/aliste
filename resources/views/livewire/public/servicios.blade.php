<?php

use App\Models\Categoria;
use App\Models\Servicio;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public string $buscar = '';
    public ?int $categoriaId = null;
    public int $porPagina = 12;

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
        $query = Servicio::query()
            ->with(['pueblo', 'categorias'])
            ->when($this->buscar, fn ($q) => $q->where(function ($sq) {
                $sq->where('nombre', 'like', "%{$this->buscar}%")
                    ->orWhereHas('pueblo', fn ($pq) => $pq->where('nombre', 'like', "%{$this->buscar}%"))
                    ->orWhereHas('categorias', fn ($cq) => $cq->where('categorias.nombre', 'like', "%{$this->buscar}%"));
            }))
            ->when($this->categoriaId, fn ($q) => $q->whereHas(
                'categorias',
                fn ($sq) => $sq->where('categorias.id', $this->categoriaId)
            ))
            ->orderBy('prioridad')
            ->orderBy('nombre');

        return [
            'total' => $query->count(),
            'servicios' => (clone $query)->take($this->porPagina)->get(),
            'categorias' => Categoria::deGrupo('servicio')
                ->withCount('servicios')
                ->having('servicios_count', '>', 0)
                ->orderByDesc('servicios_count')
                ->take(15)
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-8 sm:pt-12 pb-4">
        <h1 class="font-serif text-3xl sm:text-[38px] text-tinta mb-3">Servicios y negocios locales</h1>
        <p class="text-sm sm:text-[15px] text-tinta-muted max-w-xl leading-relaxed mb-6">
            Alojamiento, comercio, salud y oficios de toda la comarca, en un directorio siempre al día.
        </p>

        <input
            wire:model.live.debounce.300ms="buscar"
            type="text"
            placeholder="Buscar por negocio, categoría o pueblo..."
            class="w-full sm:max-w-[340px] h-11 rounded-full border border-tinta-borde bg-white dark:bg-gray-800 px-5 text-sm text-tinta placeholder:text-tinta-muted focus:outline-none focus:ring-2 focus:ring-terracota/40"
        >
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-8 pb-14 flex flex-col lg:flex-row gap-6 sm:gap-8">
        <div class="w-full lg:w-[220px] flex-shrink-0 flex flex-col gap-2">
            <button wire:click="$set('categoriaId', null)"
                class="text-left px-4 py-3 rounded-[10px] text-sm {{ ! $categoriaId ? 'bg-terracota text-white font-bold' : 'text-tinta/80' }}">
                Todos
            </button>

            <div class="flex flex-col gap-2 max-h-80 overflow-y-auto pr-1">
                @foreach ($categorias as $categoria)
                    <button wire:click="$set('categoriaId', {{ $categoria->id }})"
                        class="text-left px-4 py-3 rounded-[10px] text-sm flex justify-between gap-2 {{ $categoriaId === $categoria->id ? 'bg-terracota text-white font-bold' : 'text-tinta/80' }}">
                        <span>{{ $categoria->nombre }}</span>
                        <span class="opacity-70">{{ $categoria->servicios_count }}</span>
                    </button>
                @endforeach
            </div>

            <div class="mt-4 p-[18px] rounded-2xl bg-verde text-white">
                <div class="font-bold text-sm">¿Tienes un negocio?</div>
                <div class="text-xs mt-1.5 opacity-90 leading-relaxed">Contáctanos para añadirlo al directorio.</div>
                <a href="{{ route('contacto') }}" wire:navigate
                    class="mt-2.5 block text-center w-full bg-white dark:bg-gray-800 text-verde border-0 py-2.5 rounded-full font-bold text-xs">
                    Añadir negocio
                </a>
            </div>
        </div>

        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 content-start">
            @forelse ($servicios as $servicio)
                <div wire:key="servicio-{{ $servicio->id }}" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                    <div class="text-xs text-terracota font-bold uppercase">
                        {{ $servicio->categorias->pluck('nombre')->join(' · ') }}
                        @if ($servicio->pueblo)
                            · {{ $servicio->pueblo->nombre }}
                        @endif
                    </div>
                    <div class="font-serif font-semibold text-lg text-tinta mt-2">{{ $servicio->nombre }}</div>
                    @if ($servicio->descripcion)
                        <div class="text-[13px] text-tinta-muted mt-1.5 leading-relaxed">{{ $servicio->descripcion }}</div>
                    @endif
                    @if ($servicio->direccion)
                        <div class="text-[13px] text-tinta-muted mt-1.5">{{ $servicio->direccion }}</div>
                    @endif
                    @if ($servicio->telefono_1)
                        <a href="tel:{{ $servicio->telefono_1 }}" class="block text-[13px] text-tinta-muted mt-1">
                            {{ $servicio->telefono_1 }}{{ $servicio->telefono_2 ? ' / '.$servicio->telefono_2 : '' }}
                        </a>
                    @endif
                    @if ($servicio->sitio_web)
                        <a href="{{ $servicio->sitio_web }}" target="_blank" rel="noopener" class="block text-xs text-terracota mt-2.5">
                            {{ Str::of($servicio->sitio_web)->replace(['https://', 'http://'], '') }} ↗
                        </a>
                    @endif
                </div>
            @empty
                <p class="col-span-full text-center text-tinta-muted py-10">No se han encontrado servicios.</p>
            @endforelse
        </div>
    </div>

    @if ($total > count($servicios))
        <div class="flex justify-center pb-14">
            <button wire:click="verMas" class="bg-white dark:bg-gray-800 border-[1.5px] border-terracota text-terracota px-7 py-3 rounded-full font-bold text-sm">
                Cargar más servicios
            </button>
        </div>
    @endif
</div>
