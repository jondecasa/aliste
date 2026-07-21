<?php

use App\Models\Categoria;
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
    public ?int $categoriaId = null;
    public string $titulo = '';
    public ?string $descripcion = null;
    public ?string $lugar = null;
    public ?string $imagenActual = null;
    public $imagen = null;
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;
    public bool $esPrincipal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('gestionar-contenido-pueblo'), 403);
    }

    /**
     * Null para administradores (sin restricción). El id del pueblo del
     * redactor si el usuario solo puede gestionar su propio pueblo.
     */
    private function puebloRestringidoId(): ?int
    {
        return auth()->user()->esAdministrador() ? null : auth()->user()->pueblo_id;
    }

    public function crear(): void
    {
        $this->resetearFormulario();

        if ($puebloId = $this->puebloRestringidoId()) {
            $this->puebloId = $puebloId;
        }

        $this->dispatch('open-modal', 'evento-form');
    }

    public function editar(int $id): void
    {
        $evento = Evento::findOrFail($id);

        if ($puebloRestringido = $this->puebloRestringidoId()) {
            abort_unless($evento->pueblo_id === $puebloRestringido, 403);
        }

        $this->eventoId = $evento->id;
        $this->puebloId = $evento->pueblo_id;
        $this->categoriaId = $evento->categoria_id;
        $this->titulo = $evento->titulo;
        $this->descripcion = $evento->descripcion;
        $this->lugar = $evento->lugar;
        $this->imagenActual = $evento->imagen;
        $this->imagen = null;
        $this->fechaInicio = $evento->fecha_inicio?->format('Y-m-d\TH:i');
        $this->fechaFin = $evento->fecha_fin?->format('Y-m-d\TH:i');
        $this->esPrincipal = $evento->es_principal;

        $this->dispatch('open-modal', 'evento-form');
    }

    public function guardar(): void
    {
        $puebloRestringido = $this->puebloRestringidoId();

        if ($puebloRestringido) {
            $this->puebloId = $puebloRestringido;

            if ($this->eventoId) {
                abort_unless(Evento::whereKey($this->eventoId)->where('pueblo_id', $puebloRestringido)->exists(), 403);
            }
        }

        $datos = $this->validate([
            'puebloId' => ['required', 'exists:pueblos,id'],
            'categoriaId' => ['nullable', 'exists:categorias,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['nullable', 'date', 'after_or_equal:fechaInicio'],
            'esPrincipal' => ['boolean'],
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
                'categoria_id' => $datos['categoriaId'],
                'titulo' => $datos['titulo'],
                'slug' => Str::slug($datos['titulo']),
                'descripcion' => $datos['descripcion'],
                'lugar' => $datos['lugar'],
                'imagen' => $rutaImagen,
                'fecha_inicio' => $datos['fechaInicio'],
                'fecha_fin' => $datos['fechaFin'],
                'es_principal' => $puebloRestringido ? false : $datos['esPrincipal'],
            ]
        );

        $this->dispatch('close-modal', 'evento-form');
        $this->resetearFormulario();
    }

    public function eliminar(int $id): void
    {
        $evento = Evento::findOrFail($id);

        if ($puebloRestringido = $this->puebloRestringidoId()) {
            abort_unless($evento->pueblo_id === $puebloRestringido, 403);
        }

        if ($evento->imagen) {
            Storage::disk('public')->delete($evento->imagen);
        }

        $evento->delete();
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'eventoId', 'puebloId', 'categoriaId', 'titulo', 'descripcion', 'lugar',
            'imagenActual', 'imagen', 'fechaInicio', 'fechaFin', 'esPrincipal',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        $puebloRestringido = $this->puebloRestringidoId();

        return [
            'eventos' => Evento::query()
                ->with(['pueblo', 'categoria'])
                ->when($puebloRestringido, fn ($q) => $q->where('pueblo_id', $puebloRestringido))
                ->when($this->buscar, fn ($q) => $q->where('titulo', 'like', "%{$this->buscar}%"))
                ->orderByDesc('fecha_inicio')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
            'categorias' => Categoria::deGrupo('evento')->orderBy('nombre')->get(),
            'puebloRestringido' => $puebloRestringido,
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Principal</th>
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
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if ($evento->categoria?->color)
                                <span class="inline-block w-2.5 h-2.5 rounded-full me-1.5" style="background-color: {{ $evento->categoria->color }}"></span>
                            @endif
                            {{ $evento->titulo }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $evento->pueblo?->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $evento->fecha_inicio?->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $evento->es_principal ? 'Sí' : '' }}</td>
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
                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No hay eventos.</td>
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
                    <x-input-label value="Pueblo" />
                    @if ($puebloRestringido)
                        <p class="mt-1 py-2 text-sm text-gray-700">{{ auth()->user()->pueblo->nombre }}</p>
                    @else
                        <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Selecciona un pueblo</option>
                            @foreach ($pueblos as $pueblo)
                                <option value="{{ $pueblo->id }}">{{ $pueblo->nombre }}</option>
                            @endforeach
                        </select>
                    @endif
                    <x-input-error :messages="$errors->get('puebloId')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="titulo" value="Título" />
                    <x-text-input wire:model="titulo" id="titulo" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="categoriaId" value="Categoría" />
                    <select wire:model="categoriaId" id="categoriaId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Sin categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('categoriaId')" class="mt-2" />
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

                @unless ($puebloRestringido)
                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center">
                            <input wire:model="esPrincipal" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ms-2 text-sm text-gray-600">Es principal (aparece en el calendario de la home)</span>
                        </label>
                    </div>
                @endunless
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Guardar</x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
