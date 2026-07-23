<?php

use App\Models\Cancion;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Cancion $cancion;

    public function mount(Cancion $cancion): void
    {
        $this->cancion = $cancion->load('categorias', 'pueblo', 'audios');

        $descripcionPlana = Str::limit(strip_tags($this->cancion->descripcion ?? ''), 200);

        View::share('title', $this->cancion->titulo.' · Aliste.es');
        View::share('ogDescripcion', $descripcionPlana ?: 'Música tradicional de la comarca de Aliste, en Zamora.');
        View::share('ogImagen', $this->cancion->portada_url);
        View::share('ogUrl', route('cancion', $this->cancion));
        View::share('ogTipo', 'music.song');
    }
}; ?>

<div>
    <div class="relative h-[220px] sm:h-[340px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-foto-placeholder flex items-end">
        @if ($cancion->portada_url)
            <img src="{{ $cancion->portada_url }}" alt="{{ $cancion->titulo }}" class="absolute inset-0 w-full h-full object-cover">
        @endif
        <div class="relative w-full bg-gradient-to-t from-black/60 to-transparent p-6 sm:p-10">
            <a href="{{ route('musica') }}" wire:navigate class="text-white/80 text-xs mb-2 inline-block">← Volver a música</a>
            <h1 class="font-serif text-2xl sm:text-4xl text-white">{{ $cancion->titulo }}</h1>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-8 py-10 sm:py-14">
        <div class="text-xs text-tinta-muted mb-8">
            @if ($cancion->artista)
                {{ $cancion->artista }}
            @endif
            @if ($cancion->album)
                · {{ $cancion->album }}
            @endif
            @if ($cancion->anio)
                · {{ $cancion->anio }}
            @endif
            @if ($cancion->pueblo)
                ·
                <a href="{{ route('pueblo', $cancion->pueblo) }}" wire:navigate class="text-terracota font-semibold">{{ $cancion->pueblo->nombre }}</a>
            @endif
        </div>

        @if ($cancion->audios->isNotEmpty())
            <div class="space-y-3 mb-10">
                @foreach ($cancion->audios as $audio)
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-[0_4px_16px_rgba(60,30,10,0.06)]">
                        @if ($audio->titulo)
                            <div class="text-sm font-semibold text-tinta mb-2">{{ $audio->titulo }}</div>
                        @endif
                        <audio controls preload="none" src="{{ $audio->archivo_url }}" class="w-full"></audio>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($cancion->letra)
            <div class="mb-10">
                <div class="text-xs text-tinta-muted uppercase font-bold mb-6 tracking-wide text-center">Letra</div>
                <div class="font-serif italic text-tinta text-base sm:text-lg leading-loose whitespace-pre-line text-center max-w-xl mx-auto">{{ $cancion->letra }}</div>
            </div>
        @endif

        @if ($cancion->descripcion)
            <div class="prose prose-neutral dark:prose-invert max-w-none">
                {!! $cancion->descripcion !!}
            </div>
        @endif

        @if ($cancion->categorias->isNotEmpty())
            <div class="mt-12 pt-8 border-t border-tinta-borde">
                <div class="text-xs text-tinta-muted uppercase font-bold mb-3">Categorías</div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($cancion->categorias as $categoria)
                        <a href="{{ route('musica', ['categoriaId' => $categoria->id]) }}" wire:navigate
                            class="px-4 py-2 rounded-full text-[13px] border border-tinta-borde text-tinta/70 hover:bg-terracota hover:text-white hover:border-terracota transition">
                            {{ $categoria->nombre }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
