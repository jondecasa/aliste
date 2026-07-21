<?php

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
    </div>
</div>
