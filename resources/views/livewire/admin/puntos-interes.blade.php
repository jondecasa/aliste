<?php

use App\Models\Categoria;
use App\Models\Pueblo;
use App\Models\PuntoInteres;
use App\Support\OptimizadorImagenes;
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

    public ?int $puntoInteresId = null;
    public ?int $puebloId = null;
    public string $nombre = '';
    public ?string $descripcion = null;
    public ?string $direccion = null;
    public ?string $fotoActual = null;
    public $foto = null;
    public ?float $latitud = null;
    public ?float $longitud = null;

    /** @var array<int, int> */
    public array $categoriaIds = [];
    public ?int $idAEliminar = null;

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

        $this->dispatch('open-modal', 'punto-interes-form');
    }

    public function editar(int $id): void
    {
        $puntoInteres = PuntoInteres::findOrFail($id);

        if ($puebloRestringido = $this->puebloRestringidoId()) {
            abort_unless($puntoInteres->pueblo_id === $puebloRestringido, 403);
        }

        $this->puntoInteresId = $puntoInteres->id;
        $this->puebloId = $puntoInteres->pueblo_id;
        $this->nombre = $puntoInteres->nombre;
        $this->descripcion = $puntoInteres->descripcion;
        $this->direccion = $puntoInteres->direccion;
        $this->fotoActual = $puntoInteres->foto;
        $this->foto = null;
        $this->latitud = $puntoInteres->latitud;
        $this->longitud = $puntoInteres->longitud;
        $this->categoriaIds = $puntoInteres->categorias()->pluck('categorias.id')->all();

        $this->dispatch('open-modal', 'punto-interes-form');
    }

    public function guardar(): void
    {
        $puebloRestringido = $this->puebloRestringidoId();

        if ($puebloRestringido) {
            $this->puebloId = $puebloRestringido;

            if ($this->puntoInteresId) {
                abort_unless(PuntoInteres::whereKey($this->puntoInteresId)->where('pueblo_id', $puebloRestringido)->exists(), 403);
            }
        }

        $datos = $this->validate([
            'puebloId' => ['required', 'exists:pueblos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'categoriaIds' => ['array'],
            'categoriaIds.*' => ['exists:categorias,id'],
        ]);

        $rutaFoto = $this->fotoActual;

        if ($this->foto) {
            if ($this->fotoActual) {
                Storage::disk('public')->delete($this->fotoActual);
            }

            $rutaFoto = OptimizadorImagenes::guardar($this->foto, 'puntos-interes');
        }

        $puntoInteres = PuntoInteres::updateOrCreate(
            ['id' => $this->puntoInteresId],
            [
                'pueblo_id' => $datos['puebloId'],
                'nombre' => $datos['nombre'],
                'slug' => Str::slug($datos['nombre']),
                'descripcion' => $datos['descripcion'],
                'direccion' => $datos['direccion'],
                'foto' => $rutaFoto,
                'latitud' => $datos['latitud'],
                'longitud' => $datos['longitud'],
            ]
        );

        $puntoInteres->categorias()->sync($datos['categoriaIds']);

        $this->dispatch('close-modal', 'punto-interes-form');
        $this->resetearFormulario();
    }

    public function confirmarEliminar(int $id): void
    {
        $this->idAEliminar = $id;
    }

    public function eliminar(int $id): void
    {
        $puntoInteres = PuntoInteres::findOrFail($id);

        if ($puebloRestringido = $this->puebloRestringidoId()) {
            abort_unless($puntoInteres->pueblo_id === $puebloRestringido, 403);
        }

        if ($puntoInteres->foto) {
            Storage::disk('public')->delete($puntoInteres->foto);
        }

        $puntoInteres->delete();

        $this->idAEliminar = null;
        $this->dispatch('close-modal', 'confirmar-eliminar');
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'puntoInteresId', 'puebloId', 'nombre', 'descripcion',
            'direccion', 'fotoActual', 'foto', 'latitud', 'longitud', 'categoriaIds',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        $puebloRestringido = $this->puebloRestringidoId();

        return [
            'puntosInteres' => PuntoInteres::query()
                ->with('pueblo')
                ->when($puebloRestringido, fn ($q) => $q->where('pueblo_id', $puebloRestringido))
                ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
                ->orderBy('nombre')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
            'categoriasDisponibles' => Categoria::deGrupo('punto_interes')->orderBy('nombre')->get(),
            'puebloRestringido' => $puebloRestringido,
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Puntos de interés</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'punto-interes-form')" wire:click="crear">
            Nuevo punto de interés
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por nombre..." />
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pueblo</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($puntosInteres as $puntoInteres)
                    <tr wire:key="poi-{{ $puntoInteres->id }}">
                        <td class="px-6 py-4">
                            @if ($puntoInteres->foto_url)
                                <img src="{{ $puntoInteres->foto_url }}" alt="{{ $puntoInteres->nombre }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-900"></div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $puntoInteres->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $puntoInteres->pueblo?->nombre }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3 whitespace-nowrap">
                            <x-boton-editar wire:click="editar({{ $puntoInteres->id }})" modal="punto-interes-form" />
                            <x-boton-eliminar wire:click="confirmarEliminar({{ $puntoInteres->id }})" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">No hay puntos de interés.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $puntosInteres->links() }}
        </div>
    </div>

    <x-modal name="punto-interes-form" :show="$errors->isNotEmpty()" focusable maxWidth="xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $puntoInteresId ? 'Editar punto de interés' : 'Nuevo punto de interés' }}
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Pueblo" />
                    @if ($puebloRestringido)
                        <p class="mt-1 py-2 text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->pueblo->nombre }}</p>
                    @else
                        <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Selecciona un pueblo</option>
                            @foreach ($pueblos as $pueblo)
                                <option value="{{ $pueblo->id }}">{{ $pueblo->nombre }}</option>
                            @endforeach
                        </select>
                    @endif
                    <x-input-error :messages="$errors->get('puebloId')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input wire:model="nombre" id="nombre" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="direccion" value="Dirección" />
                    <x-text-input wire:model="direccion" id="direccion" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="foto" value="Foto" />

                    @if ($foto)
                        <img src="{{ $foto->temporaryUrl() }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @elseif ($fotoActual)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($fotoActual) }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @endif

                    <input wire:model="foto" id="foto" type="file" accept="image/*" class="mt-2 block w-full text-sm" />
                    <div wire:loading wire:target="foto" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Subiendo imagen...</div>
                    <x-input-error :messages="$errors->get('foto')" class="mt-2" />
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
                    <x-input-label value="Categorías" />
                    <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-2">
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

    <x-modal-confirmar-eliminar :id-a-eliminar="$idAEliminar" mensaje="¿Seguro que quieres eliminar este punto de interés?" />
</div>
