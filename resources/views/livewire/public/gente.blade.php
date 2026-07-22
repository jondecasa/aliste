<?php

use App\Models\Pueblo;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Pueblo $pueblo;

    public function mount(Pueblo $pueblo): void
    {
        $this->pueblo = $pueblo;
    }

    public function with(): array
    {
        return [
            'gente' => User::where('pueblo_id', $this->pueblo->id)
                ->orderBy('name')
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="relative h-[160px] sm:h-[220px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-verde flex items-end">
        <div class="relative w-full p-6 sm:p-10">
            <a href="{{ route('pueblo', $pueblo) }}" wire:navigate class="text-white/80 text-xs mb-2 inline-block">← Volver a {{ $pueblo->nombre }}</a>
            <h1 class="font-serif text-2xl sm:text-4xl text-white">Gente de {{ $pueblo->nombre }}</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-8 py-10 sm:py-14">
        @if ($gente->isEmpty())
            <p class="text-tinta-muted text-sm italic">Todavía no hay nadie registrado de {{ $pueblo->nombre }}.</p>
        @else
            <p class="text-tinta-muted text-[15px] mb-8">
                {{ $gente->count() }} {{ Str::plural('persona', $gente->count()) }} de {{ $pueblo->nombre }} registrada{{ $gente->count() === 1 ? '' : 's' }} en Aliste.es.
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-5">
                @foreach ($gente as $persona)
                    <div wire:key="persona-{{ $persona->id }}" class="flex flex-col items-center text-center">
                        @if ($persona->avatar_url)
                            <img src="{{ $persona->avatar_url }}" alt="{{ $persona->name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover shadow-[0_8px_24px_rgba(60,30,10,0.08)]">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white dark:bg-gray-800 shadow-[0_8px_24px_rgba(60,30,10,0.08)]"></div>
                        @endif
                        <div class="font-serif font-semibold text-sm sm:text-base text-tinta mt-3">{{ $persona->name }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
