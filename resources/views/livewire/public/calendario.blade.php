<?php

use App\Models\Evento;
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

    public function with(): array
    {
        $eventos = Evento::where('pueblo_id', $this->pueblo->id)
            ->with('categoria')
            ->orderBy('fecha_inicio')
            ->get();

        return [
            'eventosCalendario' => $eventos->map(fn (Evento $evento) => [
                'title' => $evento->titulo,
                'start' => $evento->fecha_inicio->toIso8601String(),
                'end' => $evento->fecha_fin?->toIso8601String(),
                'color' => $evento->categoria->color ?? '#78716c',
                'extendedProps' => [
                    'lugar' => $evento->lugar,
                    'descripcion' => $evento->descripcion,
                    'imagen' => $evento->imagen_url,
                    'categoria' => $evento->categoria->nombre ?? null,
                ],
            ]),
            'categoriasUsadas' => $eventos->pluck('categoria')->filter()->unique('id')->values(),
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

    <div class="max-w-4xl mx-auto px-4 sm:px-8 py-10 sm:py-14" x-data="{ eventoSeleccionado: null }">
        @if ($categoriasUsadas->isNotEmpty())
            <div class="flex flex-wrap gap-3 mb-6">
                @foreach ($categoriasUsadas as $categoria)
                    <div class="flex items-center gap-1.5 text-xs text-tinta-muted">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $categoria->color }}"></span>
                        {{ $categoria->nombre }}
                    </div>
                @endforeach
            </div>
        @endif

        <div
            wire:ignore
            x-init="
                const calendario = new FullCalendar.Calendar($el, {
                    plugins: [FullCalendar.dayGridPlugin, FullCalendar.listPlugin, FullCalendar.interactionPlugin],
                    initialView: 'dayGridMonth',
                    locale: FullCalendar.esLocale,
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,listMonth',
                    },
                    events: @js($eventosCalendario),
                    eventClick: (info) => {
                        eventoSeleccionado = {
                            titulo: info.event.title,
                            color: info.event.backgroundColor,
                            lugar: info.event.extendedProps.lugar,
                            descripcion: info.event.extendedProps.descripcion,
                            imagen: info.event.extendedProps.imagen,
                            categoria: info.event.extendedProps.categoria,
                            fecha: info.event.start.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
                        };
                    },
                });
                calendario.render();
            "
            class="bg-white rounded-2xl p-4 sm:p-6 shadow-[0_8px_24px_rgba(60,30,10,0.08)]"
        ></div>

        <div
            x-show="eventoSeleccionado"
            x-cloak
            class="mt-6 bg-white rounded-2xl overflow-hidden shadow-[0_8px_24px_rgba(60,30,10,0.08)] flex flex-col sm:flex-row"
        >
            <template x-if="eventoSeleccionado?.imagen">
                <div class="sm:w-56 flex-shrink-0 aspect-[16/9] sm:aspect-auto bg-foto-placeholder">
                    <img :src="eventoSeleccionado.imagen" class="w-full h-full object-cover">
                </div>
            </template>

            <div class="p-6 flex-1">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold uppercase" :style="{ color: eventoSeleccionado?.color ?? '#78716c' }" x-text="eventoSeleccionado?.categoria ?? 'Evento'"></div>
                    <button @click="eventoSeleccionado = null" class="text-tinta-muted hover:text-tinta text-sm">✕</button>
                </div>
                <div class="font-serif font-semibold text-xl text-tinta mt-1" x-text="eventoSeleccionado?.titulo"></div>
                <div class="text-sm text-tinta-muted mt-1" x-text="eventoSeleccionado?.fecha"></div>
                <template x-if="eventoSeleccionado?.lugar">
                    <div class="text-sm text-tinta-muted mt-1" x-text="eventoSeleccionado.lugar"></div>
                </template>
                <template x-if="eventoSeleccionado?.descripcion">
                    <p class="text-[15px] text-tinta-muted mt-3 leading-relaxed" x-text="eventoSeleccionado.descripcion"></p>
                </template>
            </div>
        </div>
    </div>
</div>
