<?php

use App\Models\Categoria;
use App\Models\Pueblo;
use App\Models\Servicio;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public ?int $servicioId = null;
    public ?int $puebloId = null;
    public string $nombre = '';
    public ?string $direccion = null;
    public ?string $codigoPostal = null;
    public ?string $telefono1 = null;
    public ?string $telefono2 = null;
    public ?string $sitioWeb = null;
    public ?float $latitud = null;
    public ?float $longitud = null;
    public ?string $descripcion = null;

    /** @var array<int, int> */
    public array $categoriaIds = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function crear(): void
    {
        $this->resetearFormulario();
        $this->dispatch('open-modal', 'servicio-form');
    }

    public function editar(int $id): void
    {
        $servicio = Servicio::findOrFail($id);

        $this->servicioId = $servicio->id;
        $this->puebloId = $servicio->pueblo_id;
        $this->nombre = $servicio->nombre;
        $this->direccion = $servicio->direccion;
        $this->codigoPostal = $servicio->codigo_postal;
        $this->telefono1 = $servicio->telefono_1;
        $this->telefono2 = $servicio->telefono_2;
        $this->sitioWeb = $servicio->sitio_web;
        $this->latitud = $servicio->latitud;
        $this->longitud = $servicio->longitud;
        $this->descripcion = $servicio->descripcion;
        $this->categoriaIds = $servicio->categorias()->pluck('categorias.id')->all();

        $this->dispatch('open-modal', 'servicio-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'puebloId' => ['required', 'exists:pueblos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigoPostal' => ['nullable', 'string', 'max:10'],
            'telefono1' => ['nullable', 'string', 'max:30'],
            'telefono2' => ['nullable', 'string', 'max:30'],
            'sitioWeb' => ['nullable', 'url', 'max:255'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'descripcion' => ['nullable', 'string'],
            'categoriaIds' => ['array'],
            'categoriaIds.*' => ['exists:categorias,id'],
        ]);

        $servicio = Servicio::updateOrCreate(
            ['id' => $this->servicioId],
            [
                'pueblo_id' => $datos['puebloId'],
                'nombre' => $datos['nombre'],
                'slug' => Str::slug($datos['nombre']),
                'direccion' => $datos['direccion'],
                'codigo_postal' => $datos['codigoPostal'],
                'telefono_1' => $datos['telefono1'],
                'telefono_2' => $datos['telefono2'],
                'sitio_web' => $datos['sitioWeb'],
                'latitud' => $datos['latitud'],
                'longitud' => $datos['longitud'],
                'descripcion' => $datos['descripcion'],
            ]
        );

        $servicio->categorias()->sync($datos['categoriaIds']);

        $this->dispatch('close-modal', 'servicio-form');
        $this->resetearFormulario();
    }

    public function eliminar(int $id): void
    {
        Servicio::findOrFail($id)->delete();
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'servicioId', 'puebloId', 'nombre', 'direccion', 'codigoPostal',
            'telefono1', 'telefono2', 'sitioWeb', 'latitud', 'longitud',
            'descripcion', 'categoriaIds',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'servicios' => Servicio::query()
                ->with('pueblo')
                ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
                ->orderBy('nombre')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
            'categoriasDisponibles' => Categoria::deGrupo('servicio')->orderBy('nombre')->get(),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Servicios</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'servicio-form')" wire:click="crear">
            Nuevo servicio
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por nombre..." />
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pueblo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($servicios as $servicio)
                    <tr wire:key="servicio-{{ $servicio->id }}">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $servicio->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $servicio->pueblo?->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $servicio->telefono_1 }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <button
                                wire:click="editar({{ $servicio->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'servicio-form')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >Editar</button>
                            <button
                                wire:click="eliminar({{ $servicio->id }})"
                                wire:confirm="¿Seguro que quieres eliminar este servicio?"
                                class="text-red-600 hover:text-red-900"
                            >Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No hay servicios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $servicios->links() }}
        </div>
    </div>

    <x-modal name="servicio-form" :show="$errors->isNotEmpty()" focusable maxWidth="xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $servicioId ? 'Editar servicio' : 'Nuevo servicio' }}
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
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input wire:model="nombre" id="nombre" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="direccion" value="Dirección" />
                    <x-text-input wire:model="direccion" id="direccion" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="codigoPostal" value="Código postal" />
                    <x-text-input wire:model="codigoPostal" id="codigoPostal" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('codigoPostal')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="sitioWeb" value="Sitio web" />
                    <x-text-input wire:model="sitioWeb" id="sitioWeb" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('sitioWeb')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="telefono1" value="Teléfono 1" />
                    <x-text-input wire:model="telefono1" id="telefono1" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('telefono1')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="telefono2" value="Teléfono 2" />
                    <x-text-input wire:model="telefono2" id="telefono2" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('telefono2')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="latitud" value="Latitud" />
                    <x-text-input wire:model="latitud" id="latitud" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('latitud')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="longitud" value="Longitud" />
                    <x-text-input wire:model="longitud" id="longitud" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('longitud')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Categorías" />
                    <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto">
                        @foreach ($categoriasDisponibles as $categoria)
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="categoriaIds" value="{{ $categoria->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-600">{{ $categoria->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('categoriaIds')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Guardar</x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
