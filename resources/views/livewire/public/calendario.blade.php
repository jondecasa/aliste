<?php

use App\Models\Evento;
use App\Models\Pueblo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Pueblo $pueblo;
    public int $porPagina = 6;

    public function mount(Pueblo $pueblo): void
    {
        $this->pueblo = $pueblo;
    }

    public function verMasPasados(): void
    {
        $this->porPagina += 6;
    }

    public function with(): array
    {
        $pasadosQuery = Evento::where('pueblo_id', $this->pueblo->id)
            ->where('fecha_inicio', '<', now())
            ->orderByDesc('fecha_inicio');

        return [
            'proximos' => Evento::where('pueblo_id', $this->pueblo->id)
                ->where('fecha_inicio', '>=', now())
                ->orderBy('fecha_inicio')
                ->get(),
            'totalPasados' => $pasadosQuery->count(),
            'pasados' => (clone $pasadosQuery)->take($this->porPagina)->get(),
        ];
    }
}; ?>

<div>
    <div class="relative h-[160px] sm:h-[220px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-terracota flex items-end">
        <div class="relative w-full p-6 sm:p-10">
            <a href="{{ route('pueblo', $pueblo) }}" wire:navigate class="text-white/80 text-xs mb-2 inline-block">← Volver a {{ $pueblo->nombre }}</a>
            <h1 class="font-serif text-2xl sm:text-4xl text-white">Calendario de {{ $pueblo->nombre }}</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-8 py-10 sm:py-14">
        <h2 class="font-serif text-xl sm:text-2xl text-tinta mb-6">Próximos eventos</h2>

        @if ($proximos->isEmpty())
            <p class="text-tinta-muted text-sm italic mb-14">No hay eventos próximos programados en {{ $pueblo->nombre }}.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-14">
                @foreach ($proximos as $evento)
                    <div wire:key="evento-{{ $evento->id }}" class="bg-white rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                        <div class="aspect-[16/9] bg-foto-placeholder flex items-center justify-center text-tinta-muted text-[11px]">
                            @if ($evento->imagen_url)
                                <img src="{{ $evento->imagen_url }}" alt="{{ $evento->titulo }}" class="w-full h-full object-cover">
                            @else
                                foto evento
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="text-xs text-terracota font-bold uppercase">
                                {{ $evento->fecha_inicio->translatedFormat('j \d\e F Y, H:i') }}
                            </div>
                            <div class="font-serif font-semibold text-lg text-tinta mt-1">{{ $evento->titulo }}</div>
                            @if ($evento->lugar)
                                <div class="text-sm text-tinta-muted mt-1">{{ $evento->lugar }}</div>
                            @endif
                            @if ($evento->descripcion)
                                <div class="text-[13px] text-tinta-muted mt-2 leading-relaxed">{{ $evento->descripcion }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($totalPasados > 0)
            <h2 class="font-serif text-xl sm:text-2xl text-tinta mb-6">Eventos pasados</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($pasados as $evento)
                    <div wire:key="evento-pasado-{{ $evento->id }}" class="bg-white rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)] opacity-75">
                        <div class="aspect-[16/9] bg-foto-placeholder flex items-center justify-center text-tinta-muted text-[11px]">
                            @if ($evento->imagen_url)
                                <img src="{{ $evento->imagen_url }}" alt="{{ $evento->titulo }}" class="w-full h-full object-cover">
                            @else
                                foto evento
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="text-xs text-tinta-muted font-bold uppercase">
                                {{ $evento->fecha_inicio->translatedFormat('j \d\e F Y') }}
                            </div>
                            <div class="font-serif font-semibold text-lg text-tinta mt-1">{{ $evento->titulo }}</div>
                            @if ($evento->lugar)
                                <div class="text-sm text-tinta-muted mt-1">{{ $evento->lugar }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($totalPasados > count($pasados))
                <div class="flex justify-center mt-8">
                    <button wire:click="verMasPasados" class="bg-white border-[1.5px] border-terracota text-terracota px-7 py-3 rounded-full font-bold text-sm">
                        Ver más eventos pasados
                    </button>
                </div>
            @endif
        @endif
    </div>
</div>
