<?php

use App\Models\Categoria;
use App\Models\Noticia;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public ?int $categoriaId = null;
    public int $porPagina = 6;

    public function verMas(): void
    {
        $this->porPagina += 6;
    }

    public function updated($property): void
    {
        if ($property === 'categoriaId') {
            $this->porPagina = 6;
        }
    }

    public function with(): array
    {
        $query = Noticia::query()
            ->with('categorias')
            ->when($this->categoriaId, fn ($q) => $q->whereHas(
                'categorias',
                fn ($sq) => $sq->where('categorias.id', $this->categoriaId)
            ))
            ->orderByDesc('publicado_en');

        $noticias = (clone $query)->take($this->porPagina + 1)->get();

        return [
            'total' => $query->count(),
            'destacada' => $noticias->first(),
            'resto' => $noticias->slice(1, $this->porPagina),
            'categorias' => Categoria::deGrupo('noticia')->orderBy('nombre')->get(),
        ];
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-8 sm:pt-12 pb-6 sm:pb-8">
        <h1 class="font-serif text-3xl sm:text-[38px] text-tinta mb-4">Noticias y blog de la comarca</h1>

        <div class="flex gap-2 flex-wrap">
            <button wire:click="$set('categoriaId', null)"
                class="px-4 py-2.5 rounded-full text-[13px] {{ ! $categoriaId ? 'bg-terracota text-white font-semibold' : 'border border-tinta-borde text-tinta/70' }}">
                Todas
            </button>
            @foreach ($categorias as $categoria)
                <button wire:click="$set('categoriaId', {{ $categoria->id }})"
                    class="px-4 py-2.5 rounded-full text-[13px] {{ $categoriaId === $categoria->id ? 'bg-terracota text-white font-semibold' : 'border border-tinta-borde text-tinta/70' }}">
                    {{ $categoria->nombre }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($destacada)
        <div class="max-w-7xl mx-auto px-4 sm:px-8 pb-8">
            <div class="flex flex-col sm:flex-row gap-0 sm:gap-8 bg-white rounded-[20px] overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                <div class="sm:w-[440px] flex-shrink-0 aspect-[16/9] sm:aspect-auto bg-foto-placeholder flex items-center justify-center text-tinta-muted text-xs">
                    @if ($destacada->imagen_portada)
                        <img src="{{ $destacada->imagen_portada }}" alt="{{ $destacada->titulo }}" class="w-full h-full object-cover">
                    @else
                        foto destacada
                    @endif
                </div>
                <div class="p-6 sm:p-8 sm:pl-0 flex flex-col justify-center">
                    <div class="text-xs text-terracota font-bold uppercase">
                        {{ $destacada->categorias->pluck('nombre')->join(' · ') }}
                        @if ($destacada->publicado_en)
                            · {{ $destacada->publicado_en->translatedFormat('j \d\e F Y') }}
                        @endif
                    </div>
                    <div class="font-serif text-xl sm:text-[26px] font-semibold text-tinta mt-2">{{ $destacada->titulo }}</div>
                    @if ($destacada->extracto)
                        <div class="text-sm text-tinta-muted mt-2.5 leading-relaxed">{{ $destacada->extracto }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-8 pb-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        @forelse ($resto as $noticia)
            <div wire:key="noticia-{{ $noticia->id }}">
                <div class="aspect-[16/10] rounded-[14px] bg-foto-placeholder flex items-center justify-center text-tinta-muted text-[11px]">
                    @if ($noticia->imagen_portada)
                        <img src="{{ $noticia->imagen_portada }}" alt="{{ $noticia->titulo }}" class="w-full h-full object-cover rounded-[14px]">
                    @else
                        foto noticia
                    @endif
                </div>
                <div class="text-xs text-tinta-muted mt-3">
                    {{ $noticia->publicado_en?->translatedFormat('j \d\e F Y') }}
                    @if ($noticia->categorias->isNotEmpty())
                        · {{ $noticia->categorias->first()->nombre }}
                    @endif
                </div>
                <div class="font-serif font-semibold text-base sm:text-[17px] text-tinta mt-1">{{ $noticia->titulo }}</div>
            </div>
        @empty
            @if (! $destacada)
                <p class="col-span-full text-center text-tinta-muted py-10">Todavía no hay noticias publicadas.</p>
            @endif
        @endforelse
    </div>

    @if ($total > (count($resto) + ($destacada ? 1 : 0)))
        <div class="flex justify-center pb-14">
            <button wire:click="verMas" class="bg-white border-[1.5px] border-terracota text-terracota px-7 py-3 rounded-full font-bold text-sm">
                Cargar más noticias
            </button>
        </div>
    @endif
</div>
