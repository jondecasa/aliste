<?php

use App\Models\Categoria;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public const GRUPOS = [
        'noticia' => 'Noticia',
        'punto_interes' => 'Punto de interés',
        'servicio' => 'Servicio',
        'cancion' => 'Canción',
        'obra_literaria' => 'Obra literaria',
        'evento' => 'Evento',
    ];

    public string $buscar = '';
    public string $filtroGrupo = '';

    public ?int $categoriaId = null;
    public string $nombre = '';
    public string $grupo = 'noticia';
    public ?string $color = '#e11d48';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function crear(): void
    {
        $this->resetearFormulario();
        $this->dispatch('open-modal', 'categoria-form');
    }

    public function editar(int $id): void
    {
        $categoria = Categoria::findOrFail($id);

        $this->categoriaId = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->grupo = $categoria->grupo;
        $this->color = $categoria->color ?? '#e11d48';

        $this->dispatch('open-modal', 'categoria-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'grupo' => ['required', 'string', 'in:'.implode(',', array_keys(self::GRUPOS))],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        Categoria::updateOrCreate(
            ['id' => $this->categoriaId],
            [
                'nombre' => $datos['nombre'],
                'slug' => Str::slug($datos['nombre']),
                'grupo' => $datos['grupo'],
                'color' => $datos['grupo'] === 'evento' ? $datos['color'] : null,
            ]
        );

        $this->dispatch('close-modal', 'categoria-form');
        $this->resetearFormulario();
    }

    public function eliminar(int $id): void
    {
        Categoria::findOrFail($id)->delete();
    }

    private function resetearFormulario(): void
    {
        $this->reset(['categoriaId', 'nombre']);
        $this->grupo = 'noticia';
        $this->color = '#e11d48';
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'categorias' => Categoria::query()
                ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
                ->when($this->filtroGrupo, fn ($q) => $q->where('grupo', $this->filtroGrupo))
                ->orderBy('grupo')
                ->orderBy('nombre')
                ->paginate(20),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categorías</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'categoria-form')" wire:click="crear">
            Nueva categoría
        </x-primary-button>
    </div>

    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por nombre..." />

        <select wire:model.live="filtroGrupo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Todos los grupos</option>
            @foreach (self::GRUPOS as $valor => $etiqueta)
                <option value="{{ $valor }}">{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grupo</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($categorias as $categoria)
                    <tr wire:key="categoria-{{ $categoria->id }}">
                        <td class="px-6 py-4">
                            @if ($categoria->color)
                                <span class="inline-block w-4 h-4 rounded-full" style="background-color: {{ $categoria->color }}"></span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $categoria->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ self::GRUPOS[$categoria->grupo] }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <button
                                wire:click="editar({{ $categoria->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'categoria-form')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >Editar</button>
                            <button
                                wire:click="eliminar({{ $categoria->id }})"
                                wire:confirm="¿Seguro que quieres eliminar esta categoría?"
                                class="text-red-600 hover:text-red-900"
                            >Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No hay categorías.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $categorias->links() }}
        </div>
    </div>

    <x-modal name="categoria-form" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $categoriaId ? 'Editar categoría' : 'Nueva categoría' }}
            </h2>

            <div class="mt-6 space-y-4">
                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input wire:model="nombre" id="nombre" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="grupo" value="Grupo" />
                    <select wire:model.live="grupo" id="grupo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        @foreach (self::GRUPOS as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('grupo')" class="mt-2" />
                </div>

                @if ($grupo === 'evento')
                    <div>
                        <x-input-label for="color" value="Color en el calendario" />
                        <input wire:model="color" id="color" type="color" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm">
                        <x-input-error :messages="$errors->get('color')" class="mt-2" />
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Guardar</x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
