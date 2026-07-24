<?php

use App\Models\RegistroLog;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroTipo = '';
    public ?int $verId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function ver(int $id): void
    {
        $this->verId = $id;
        $this->dispatch('open-modal', 'log-detalle');
    }

    public function with(): array
    {
        return [
            'logs' => RegistroLog::query()
                ->when($this->filtroTipo, fn ($q) => $q->where('tipo', $this->filtroTipo))
                ->when($this->buscar, fn ($q) => $q->where(function ($sq) {
                    $sq->where('mensaje', 'like', "%{$this->buscar}%")
                        ->orWhere('origen', 'like', "%{$this->buscar}%");
                }))
                ->latest()
                ->paginate(25),
            'registroSeleccionado' => $this->verId ? RegistroLog::find($this->verId) : null,
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Registros</h2>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        Errores, excepciones y lanzamientos de tareas programadas, registrados automáticamente.
    </p>

    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por mensaje u origen..." />

        <select wire:model.live="filtroTipo" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Todos los tipos</option>
            @foreach (RegistroLog::TIPOS as $valor => $etiqueta)
                <option value="{{ $valor }}">{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Origen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Mensaje</th>
                        <th class="px-6 py-3 sticky right-0 bg-gray-50 dark:bg-gray-700/50"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($logs as $log)
                        <tr wire:key="log-{{ $log->id }}">
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <span @class([
                                    'px-2.5 py-1 rounded-full text-xs font-semibold',
                                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $log->tipo === \App\Models\RegistroLog::TIPO_ERROR,
                                    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $log->tipo === \App\Models\RegistroLog::TIPO_EXCEPCION,
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' => $log->tipo === \App\Models\RegistroLog::TIPO_INFORMACION,
                                ])>
                                    {{ \App\Models\RegistroLog::TIPOS[$log->tipo] ?? $log->tipo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $log->origen }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 max-w-md truncate">{{ $log->mensaje }}</td>
                            <td class="px-6 py-4 text-right text-sm whitespace-nowrap sticky right-0 bg-white dark:bg-gray-800">
                                <button
                                    wire:click="ver({{ $log->id }})"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition"
                                    title="Ver detalle"
                                >
                                    <span class="sr-only">Ver detalle</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                        <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                        <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $logs->links() }}
        </div>
    </div>

    <x-modal name="log-detalle" maxWidth="2xl">
        <div class="p-6">
            @if ($registroSeleccionado)
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Detalle del registro #{{ $registroSeleccionado->id }}
                </h2>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Fecha</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $registroSeleccionado->created_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Tipo</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ \App\Models\RegistroLog::TIPOS[$registroSeleccionado->tipo] ?? $registroSeleccionado->tipo }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Origen</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $registroSeleccionado->origen ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Mensaje</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $registroSeleccionado->mensaje }}</dd>
                    </div>
                    @if ($registroSeleccionado->contexto)
                        <div>
                            <dt class="font-semibold text-gray-500 dark:text-gray-400 mb-1">Contexto</dt>
                            <dd>
                                <pre class="bg-gray-50 dark:bg-gray-900 rounded-md p-3 text-xs overflow-x-auto whitespace-pre-wrap break-words">{{ json_encode($registroSeleccionado->contexto, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            </dd>
                        </div>
                    @endif
                </dl>
            @endif

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cerrar</x-secondary-button>
            </div>
        </div>
    </x-modal>
</div>
