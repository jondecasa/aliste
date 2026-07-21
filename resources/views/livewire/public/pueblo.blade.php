<?php

use App\Models\Noticia;
use App\Models\Pueblo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Pueblo $pueblo;

    public function mount(Pueblo $pueblo): void
    {
        $this->pueblo = $pueblo;
    }

    public function getMapaUrlProperty(): ?string
    {
        if (! $this->pueblo->latitud || ! $this->pueblo->longitud) {
            return null;
        }

        $lat = (float) $this->pueblo->latitud;
        $lon = (float) $this->pueblo->longitud;

        $bbox = ($lon - 0.012).','.($lat - 0.008).','.($lon + 0.012).','.($lat + 0.008);

        return "https://www.openstreetmap.org/export/embed.html?bbox={$bbox}&layer=mapnik&marker={$lat},{$lon}";
    }

    public function with(): array
    {
        return [
            'noticias' => Noticia::where('pueblo_id', $this->pueblo->id)
                ->orderByDesc('publicado_en')
                ->take(6)
                ->get(),
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

    <div class="max-w-3xl mx-auto px-4 sm:px-8 py-10 sm:py-14">
        @if ($pueblo->poblacion || $pueblo->altitud || $this->mapaUrl)
            <div class="grid grid-cols-2 {{ $this->mapaUrl ? 'sm:grid-cols-4' : 'sm:grid-cols-2' }} gap-4 mb-8">
                @if ($pueblo->poblacion)
                    <div class="bg-white rounded-2xl p-5 shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                        <div class="text-2xl sm:text-3xl font-serif font-semibold text-terracota">{{ $pueblo->poblacion }}</div>
                        <div class="text-xs text-tinta-muted mt-1">Habitantes</div>
                    </div>
                @endif

                @if ($pueblo->altitud)
                    <div class="bg-white rounded-2xl p-5 shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                        <div class="text-2xl sm:text-3xl font-serif font-semibold text-terracota">{{ $pueblo->altitud }} <span class="text-base font-sans font-normal text-tinta-muted">m</span></div>
                        <div class="text-xs text-tinta-muted mt-1">Altitud</div>
                    </div>
                @endif

                @if ($this->mapaUrl)
                    <div class="col-span-2 rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)] h-40 sm:h-auto">
                        <iframe
                            src="{{ $this->mapaUrl }}"
                            class="w-full h-full border-0"
                            loading="lazy"
                            title="Mapa de {{ $pueblo->nombre }}"
                        ></iframe>
                    </div>
                @endif
            </div>
        @endif

        @if ($pueblo->descripcion)
            <p class="text-tinta-muted text-[15px] leading-relaxed mb-8">{{ $pueblo->descripcion }}</p>
        @endif

        @if ($pueblo->contenido_html)
            <div class="prose prose-neutral max-w-none">
                {!! $pueblo->contenido_html !!}
            </div>
        @else
            <p class="text-tinta-muted text-sm italic">Todavía no hay contenido para este pueblo.</p>
        @endif

        @if ($noticias->isNotEmpty())
            <div class="mt-14 pt-10 border-t border-tinta-borde">
                <h2 class="font-serif text-xl sm:text-2xl text-tinta mb-6">Últimas noticias de {{ $pueblo->nombre }}</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach ($noticias as $noticia)
                        <a href="{{ route('blog') }}" wire:navigate wire:key="noticia-{{ $noticia->id }}" class="block">
                            <div class="aspect-[16/10] rounded-[14px] bg-foto-placeholder flex items-center justify-center text-tinta-muted text-[11px]">
                                @if ($noticia->imagen_portada)
                                    <img src="{{ $noticia->imagen_portada }}" alt="{{ $noticia->titulo }}" class="w-full h-full object-cover rounded-[14px]">
                                @else
                                    foto noticia
                                @endif
                            </div>
                            <div class="text-xs text-tinta-muted mt-3">{{ $noticia->publicado_en?->translatedFormat('j \d\e F Y') }}</div>
                            <div class="font-serif font-semibold text-base text-tinta mt-1">{{ $noticia->titulo }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
