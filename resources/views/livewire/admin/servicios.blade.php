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
    public bool $soloNoVistos = false;
    public bool $ocultarRevisados = false;

    public ?int $servicioId = null;
    public ?int $puebloId = null;
    public string $nombre = '';
    public int $prioridad = 5;
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
    public ?int $idAEliminar = null;

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
        $this->prioridad = $servicio->prioridad;
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
            'prioridad' => ['required', 'integer', 'min:1', 'max:255'],
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
                'prioridad' => $datos['prioridad'],
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

    public function confirmarEliminar(int $id): void
    {
        $this->idAEliminar = $id;
    }

    public function marcarRevisado(int $id): void
    {
        Servicio::whereKey($id)->update(['revisado_en' => now()]);
    }

    public function eliminar(int $id): void
    {
        Servicio::findOrFail($id)->delete();

        $this->idAEliminar = null;
        $this->dispatch('close-modal', 'confirmar-eliminar');
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'servicioId', 'puebloId', 'nombre', 'prioridad', 'direccion', 'codigoPostal',
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
                ->when($this->soloNoVistos, fn ($q) => $q->noVistosEnUltimaImportacion())
                ->when($this->ocultarRevisados, fn ($q) => $q->whereNull('revisado_en'))
                ->orderBy('prioridad')
                ->orderBy('nombre')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
            'categoriasDisponibles' => Categoria::deGrupo('servicio')->orderBy('nombre')->get(),
            'totalNoVistos' => Servicio::noVistosEnUltimaImportacion()->count(),
            'totalPendientesRevision' => Servicio::whereNull('revisado_en')->count(),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Servicios</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'servicio-form')" wire:click="crear">
            Nuevo servicio
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por nombre..." />
    </div>

    <div class="mb-4">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" wire:model.live="soloNoVistos" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Mostrar solo los que no aparecieron en la última importación de aliste.info ({{ $totalNoVistos }})
            </span>
        </label>
        @if ($totalNoVistos > 0)
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Puede que hayan cerrado o desaparecido de la fuente original — revisa cada uno antes de borrarlo. Los servicios creados a mano en este panel nunca aparecen aquí.
            </p>
        @endif
    </div>

    <div class="mb-4">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" wire:model.live="ocultarRevisados" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Ocultar ya revisados ({{ $totalPendientesRevision }} pendientes de revisar)
            </span>
        </label>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            aliste.info lleva años sin actualizarse, así que no sirve para saber qué negocios siguen abiertos de verdad. Usa el botón de mapa de cada fila para comprobarlo en Google Maps y márcalo como revisado cuando lo confirmes.
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pueblo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prioridad</th>
                        @if ($soloNoVistos)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Visto por última vez</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Revisado</th>
                        <th class="px-6 py-3 sticky right-0 bg-gray-50 dark:bg-gray-700/50"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($servicios as $servicio)
                        <tr wire:key="servicio-{{ $servicio->id }}">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $servicio->nombre }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $servicio->pueblo?->nombre }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $servicio->telefono_1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $servicio->prioridad }}</td>
                            @if ($soloNoVistos)
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $servicio->visto_en_importacion_en?->format('d/m/Y') ?? '—' }}
                                </td>
                            @endif
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                @if ($servicio->revisado_en)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                        {{ $servicio->revisado_en->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">Sin revisar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm space-x-3 whitespace-nowrap sticky right-0 bg-white dark:bg-gray-800">
                                <a
                                    href="{{ $servicio->enlace_maps }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
                                    title="Ver en Google Maps"
                                >
                                    <span class="sr-only">Ver en Google Maps</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                @unless ($servicio->revisado_en)
                                    <button
                                        type="button"
                                        wire:click="marcarRevisado({{ $servicio->id }})"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-green-50 text-green-700 hover:bg-green-100 transition"
                                        title="Marcar como revisado"
                                    >
                                        <span class="sr-only">Marcar como revisado</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endunless
                                <x-boton-editar wire:click="editar({{ $servicio->id }})" modal="servicio-form" />
                                <x-boton-eliminar wire:click="confirmarEliminar({{ $servicio->id }})" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $soloNoVistos ? 7 : 6 }}" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">No hay servicios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $servicios->links() }}
        </div>
    </div>

    <x-modal name="servicio-form" :show="$errors->isNotEmpty()" focusable maxWidth="xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $servicioId ? 'Editar servicio' : 'Nuevo servicio' }}
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="puebloId" value="Pueblo" />
                    <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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

                <div>
                    <x-input-label for="prioridad" value="Prioridad" />
                    <x-text-input wire:model="prioridad" id="prioridad" type="number" min="1" max="255" class="mt-1 block w-full" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cuanto más baja, antes aparece en /servicios. Por defecto: 5.</p>
                    <x-input-error :messages="$errors->get('prioridad')" class="mt-2" />
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
                    <textarea wire:model="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Categorías" />
                    <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto">
                        @foreach ($categoriasDisponibles as $categoria)
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="categoriaIds" value="{{ $categoria->id }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $categoria->nombre }}</span>
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

    <x-modal-confirmar-eliminar :id-a-eliminar="$idAEliminar" mensaje="¿Seguro que quieres eliminar este servicio?" />
</div>
