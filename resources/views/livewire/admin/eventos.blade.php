<?php

use App\Models\Evento;
use App\Models\Pueblo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination, WithFileUploads;

    public string $buscar = '';

    public ?int $eventoId = null;
    public ?int $puebloId = null;
    public string $titulo = '';
    public ?string $descripcion = null;
    public ?string $lugar = null;
    public ?string $imagenActual = null;
    public $imagen = null;
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function crear(): void
    {
        $this->resetearFormulario();
        $this->dispatch('open-modal', 'evento-form');
    }

    public function editar(int $id): void
    {
        $evento = Evento::findOrFail($id);

        $this->eventoId = $evento->id;
        $this->puebloId = $evento->pueblo_id;
        $this->titulo = $evento->titulo;
        $this->descripcion = $evento->descripcion;
        $this->lugar = $evento->lugar;
        $this->imagenActual = $evento->imagen;
        $this->imagen = null;
        $this->fechaInicio = $evento->fecha_inicio?->format('Y-m-d\TH:i');
        $this->fechaFin = $evento->fecha_fin?->format('Y-m-d\TH:i');

        $this->dispatch('open-modal', 'evento-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'puebloId' => ['required', 'exists:pueblos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['nullable', 'date', 'after_or_equal:fechaInicio'],
        ]);

        $rutaImagen = $this->imagenActual;

        if ($this->imagen) {
            if ($this->imagenActual) {
                Storage::disk('public')->delete($this->imagenActual);
            }

            $rutaImagen = $this->imagen->store('eventos', 'public');
        }

        Evento::updateOrCreate(
            ['id' => $this->eventoId],
            [
                'pueblo_id' => $datos['puebloId'],
                'titulo' => $datos['titulo'],
                'slug' => Str::slug($datos['titulo']),
                'descripcion' => $datos['descripcion'],
                'lugar' => $datos['lugar'],
                'imagen' => $rutaImagen,
                'fecha_inicio' => $datos['fechaInicio'],
                'fecha_fin' => $datos['fechaFin'],
            ]
        );

        $this->dispatch('close-modal', 'evento-form');
        $this->resetearFormulario();
    }

    public function eliminar(int $id): void
    {
        $evento = Evento::findOrFail($id);

        if ($evento->imagen) {
            Storage::disk('public')->delete($evento->imagen);
        }

        $evento->delete();
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'eventoId', 'puebloId', 'titulo', 'descripcion', 'lugar',
            'imagenActual', 'imagen', 'fechaInicio', 'fechaFin',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'eventos' => Evento::query()
                ->with('pueblo')
                ->when($this->buscar, fn ($q) => $q->where('titulo', 'like', "%{$this->buscar}%"))
                ->orderByDesc('fecha_inicio')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Eventos</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'evento-form')" wire:click="crear">
            Nuevo evento
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por título..." />
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pueblo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($eventos as $evento)
                    <tr wire:key="evento-{{ $evento->id }}">
                        <td class="px-6 py-4">
                            @if ($evento->imagen_url)
                                <img src="{{ $evento->imagen_url }}" alt="{{ $evento->titulo }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100"></div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $evento->titulo }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $evento->pueblo?->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $evento->fecha_inicio?->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <button
                                wire:click="editar({{ $evento->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'evento-form')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >Editar</button>
                            <button
                                wire:click="eliminar({{ $evento->id }})"
                                wire:confirm="¿Seguro que quieres eliminar este evento?"
                                class="text-red-600 hover:text-red-900"
                            >Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No hay eventos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $eventos->links() }}
        </div>
    </div>

    <x-modal name="evento-form" :show="$errors->isNotEmpty()" focusable maxWidth="xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $eventoId ? 'Editar evento' : 'Nuevo evento' }}
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="puebloId" value="Pueblo" />
                    <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Selecciona un pueblo</option>
                        @foreach ($pueblos as $pueblo)
                            <option value="{{ $pueblo->id }}">{{ $pueblo->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('puebloId')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="titulo" value="Título" />
                    <x-text-input wire:model="titulo" id="titulo" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="fechaInicio" value="Fecha y hora de inicio" />
                    <x-text-input wire:model="fechaInicio" id="fechaInicio" type="datetime-local" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('fechaInicio')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="fechaFin" value="Fecha y hora de fin (opcional)" />
                    <x-text-input wire:model="fechaFin" id="fechaFin" type="datetime-local" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('fechaFin')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="lugar" value="Lugar" />
                    <x-text-input wire:model="lugar" id="lugar" type="text" class="mt-1 block w-full" placeholder="Ej: Plaza Mayor" />
                    <x-input-error :messages="$errors->get('lugar')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="imagen" value="Imagen" />

                    @if ($imagen)
                        <img src="{{ $imagen->temporaryUrl() }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @elseif ($imagenActual)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($imagenActual) }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @endif

                    <input wire:model="imagen" id="imagen" type="file" accept="image/*" class="mt-2 block w-full text-sm" />
                    <div wire:loading wire:target="imagen" class="text-xs text-gray-500 mt-1">Subiendo imagen...</div>
                    <x-input-error :messages="$errors->get('imagen')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Guardar</x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
