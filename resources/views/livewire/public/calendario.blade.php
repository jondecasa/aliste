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
                'start' => $evento->inicio_calendario->toIso8601String(),
                'end' => $evento->fecha_fin?->toIso8601String(),
                'color' => $evento->categoria->color ?? '#78716c',
                'extendedProps' => [
                    'lugar' => $evento->lugar,
                    'descripcion' => $evento->descripcion,
                    'imagen' => $evento->imagen_url,
                    'categoria' => $evento->categoria->nombre ?? null,
                    'ordenLogico' => $evento->orden_logico,
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

    <div
        class="max-w-7xl mx-auto px-4 sm:px-8 py-10 sm:py-14 flex flex-col lg:flex-row gap-8"
        x-data="{ diaSeleccionado: null, eventosDelDia: [], eventoSeleccionado: null }"
    >
        <div class="flex-1 min-w-0">
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
                    const todosEventos = @js($eventosCalendario);

                    const eventosDeFecha = (fechaISO) => todosEventos.filter((e) => e.start.slice(0, 10) === fechaISO);

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
                        events: todosEventos,
                        eventOrder: (a, b) => (a.extendedProps.ordenLogico ?? 0) - (b.extendedProps.ordenLogico ?? 0),
                        dateClick: (info) => {
                            eventoSeleccionado = null;
                            diaSeleccionado = info.dateStr;
                            eventosDelDia = eventosDeFecha(info.dateStr);
                        },
                        eventClick: (info) => {
                            const fechaISO = info.event.startStr.slice(0, 10);
                            diaSeleccionado = fechaISO;
                            eventosDelDia = eventosDeFecha(fechaISO);
                            eventoSeleccionado = {
                                titulo: info.event.title,
                                color: info.event.backgroundColor,
                                lugar: info.event.extendedProps.lugar,
                                descripcion: info.event.extendedProps.descripcion,
                                imagen: info.event.extendedProps.imagen,
                                categoria: info.event.extendedProps.categoria,
                                hora: info.event.start.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }),
                            };
                        },
                    });
                    calendario.render();
                "
                class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-6 shadow-[0_8px_24px_rgba(60,30,10,0.08)]"
            ></div>
        </div>

        <div class="w-full lg:w-[320px] flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-[0_8px_24px_rgba(60,30,10,0.08)] sticky top-6 overflow-hidden">
                <template x-if="!diaSeleccionado">
                    <div class="p-6 text-sm text-tinta-muted italic">
                        Haz clic en un día del calendario para ver sus eventos.
                    </div>
                </template>

                <template x-if="diaSeleccionado && !eventoSeleccionado">
                    <div>
                        <div class="bg-tinta text-white px-5 py-4 font-serif font-semibold text-lg capitalize" x-text="new Date(diaSeleccionado + 'T00:00:00').toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' })"></div>
                        <div class="p-5">
                            <template x-if="eventosDelDia.length === 0">
                                <p class="text-sm text-tinta-muted italic">No hay eventos este día.</p>
                            </template>

                            <div class="flex flex-col gap-4">
                                <template x-for="evento in eventosDelDia" :key="evento.title + evento.start">
                                    <button
                                        @click="eventoSeleccionado = {
                                            titulo: evento.title,
                                            color: evento.color,
                                            lugar: evento.extendedProps.lugar,
                                            descripcion: evento.extendedProps.descripcion,
                                            imagen: evento.extendedProps.imagen,
                                            categoria: evento.extendedProps.categoria,
                                            hora: new Date(evento.start).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }),
                                        }"
                                        class="text-left block"
                                    >
                                        <div class="flex items-center gap-1.5 text-xs font-bold uppercase" :style="{ color: evento.color }" x-text="evento.extendedProps.categoria ?? 'Evento'"></div>
                                        <div class="font-serif font-semibold text-sm text-tinta mt-0.5" x-text="evento.title"></div>
                                        <div class="text-xs text-tinta-muted mt-0.5" x-text="new Date(evento.start).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })"></div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="eventoSeleccionado">
                    <div>
                        <template x-if="eventoSeleccionado?.imagen">
                            <div class="aspect-[16/9] bg-foto-placeholder">
                                <img :src="eventoSeleccionado.imagen" class="w-full h-full object-cover">
                            </div>
                        </template>

                        <div class="p-5">
                            <button @click="eventoSeleccionado = null" class="text-xs text-tinta-muted hover:text-tinta mb-3">← Volver al día</button>

                            <div class="text-xs font-bold uppercase" :style="{ color: eventoSeleccionado?.color ?? '#78716c' }" x-text="eventoSeleccionado?.categoria ?? 'Evento'"></div>
                            <div class="font-serif font-semibold text-lg text-tinta mt-1" x-text="eventoSeleccionado?.titulo"></div>
                            <div class="text-sm text-tinta-muted mt-1" x-text="eventoSeleccionado?.hora"></div>
                            <template x-if="eventoSeleccionado?.lugar">
                                <div class="text-sm text-tinta-muted mt-1" x-text="eventoSeleccionado.lugar"></div>
                            </template>
                            <template x-if="eventoSeleccionado?.descripcion">
                                <p class="text-[15px] text-tinta-muted mt-3 leading-relaxed" x-text="eventoSeleccionado.descripcion"></p>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
