<?php

use App\Models\Noticia;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Noticia $noticia;

    public function mount(Noticia $noticia): void
    {
        $this->noticia = $noticia->load('categorias', 'pueblo');
    }
}; ?>

<div>
    <div class="relative h-[220px] sm:h-[340px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-foto-placeholder flex items-end">
        @if ($noticia->imagen_portada)
            <img src="{{ $noticia->imagen_portada }}" alt="{{ $noticia->titulo }}" class="absolute inset-0 w-full h-full object-cover">
        @endif
        <div class="relative w-full bg-gradient-to-t from-black/60 to-transparent p-6 sm:p-10">
            <a href="{{ route('noticias') }}" wire:navigate class="text-white/80 text-xs mb-2 inline-block">← Volver a noticias</a>
            <h1 class="font-serif text-2xl sm:text-4xl text-white">{{ $noticia->titulo }}</h1>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-8 py-10 sm:py-14">
        <div class="text-xs text-tinta-muted mb-8">
            @if ($noticia->publicado_en)
                {{ $noticia->publicado_en->translatedFormat('j \d\e F \d\e Y') }}
            @endif
            @if ($noticia->pueblo)
                · {{ $noticia->pueblo->nombre }}
            @endif
            @if ($noticia->fuente_nombre)
                · vía
                @if ($noticia->fuente_url)
                    <a href="{{ $noticia->fuente_url }}" target="_blank" rel="noopener" class="text-terracota">{{ $noticia->fuente_nombre }}</a>
                @else
                    {{ $noticia->fuente_nombre }}
                @endif
            @endif
        </div>

        @if ($noticia->extracto)
            <p class="text-tinta-muted text-[15px] leading-relaxed mb-8 italic">{{ $noticia->extracto }}</p>
        @endif

        @if ($noticia->cuerpo)
            <div class="prose prose-neutral max-w-none">
                {!! $noticia->cuerpo !!}
            </div>
        @endif

        @if ($noticia->url_externa)
            <a href="{{ $noticia->url_externa }}" target="_blank" rel="noopener" class="inline-block mt-6 text-sm text-terracota font-semibold">
                Ver artículo original ↗
            </a>
        @endif

        @if ($noticia->categorias->isNotEmpty())
            <div class="mt-12 pt-8 border-t border-tinta-borde">
                <div class="text-xs text-tinta-muted uppercase font-bold mb-3">Categorías</div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($noticia->categorias as $categoria)
                        <a href="{{ route('noticias', ['categoriaId' => $categoria->id]) }}" wire:navigate
                            class="px-4 py-2 rounded-full text-[13px] border border-tinta-borde text-tinta/70 hover:bg-terracota hover:text-white hover:border-terracota transition">
                            {{ $categoria->nombre }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
