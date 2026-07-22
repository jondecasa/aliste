<?php

use App\Models\Categoria;
use App\Models\ObraLiteraria;
use App\Models\Pueblo;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public const TIPOS = [
        'poesia' => 'Poesía',
        'relato' => 'Relato',
        'novela' => 'Novela',
        'ensayo' => 'Ensayo',
    ];

    public string $buscar = '';

    public ?int $obraId = null;
    public ?int $puebloId = null;
    public string $titulo = '';
    public ?string $autor = null;
    public ?string $tipoObra = null;
    public ?string $archivo = null;
    public ?int $anio = null;
    public ?int $paginas = null;
    public ?string $portada = null;
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
        $this->dispatch('open-modal', 'obra-form');
    }

    public function editar(int $id): void
    {
        $obra = ObraLiteraria::findOrFail($id);

        $this->obraId = $obra->id;
        $this->puebloId = $obra->pueblo_id;
        $this->titulo = $obra->titulo;
        $this->autor = $obra->autor;
        $this->tipoObra = $obra->tipo_obra;
        $this->archivo = $obra->archivo;
        $this->anio = $obra->anio;
        $this->paginas = $obra->paginas;
        $this->portada = $obra->portada;
        $this->descripcion = $obra->descripcion;
        $this->categoriaIds = $obra->categorias()->pluck('categorias.id')->all();

        $this->dispatch('open-modal', 'obra-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'puebloId' => ['nullable', 'exists:pueblos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'autor' => ['nullable', 'string', 'max:255'],
            'tipoObra' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::TIPOS))],
            'archivo' => ['nullable', 'string', 'max:255'],
            'anio' => ['nullable', 'integer', 'min:0', 'max:'.(date('Y') + 1)],
            'paginas' => ['nullable', 'integer', 'min:0'],
            'portada' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'categoriaIds' => ['array'],
            'categoriaIds.*' => ['exists:categorias,id'],
        ]);

        $obra = ObraLiteraria::updateOrCreate(
            ['id' => $this->obraId],
            [
                'pueblo_id' => $datos['puebloId'],
                'titulo' => $datos['titulo'],
                'slug' => Str::slug($datos['titulo']),
                'autor' => $datos['autor'],
                'tipo_obra' => $datos['tipoObra'],
                'archivo' => $datos['archivo'],
                'anio' => $datos['anio'],
                'paginas' => $datos['paginas'],
                'portada' => $datos['portada'],
                'descripcion' => $datos['descripcion'],
            ]
        );

        $obra->categorias()->sync($datos['categoriaIds']);

        $this->dispatch('close-modal', 'obra-form');
        $this->resetearFormulario();
    }

    public function confirmarEliminar(int $id): void
    {
        $this->idAEliminar = $id;
    }

    public function eliminar(int $id): void
    {
        ObraLiteraria::findOrFail($id)->delete();

        $this->idAEliminar = null;
        $this->dispatch('close-modal', 'confirmar-eliminar');
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'obraId', 'puebloId', 'titulo', 'autor', 'tipoObra',
            'archivo', 'anio', 'paginas', 'portada', 'descripcion', 'categoriaIds',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'obras' => ObraLiteraria::query()
                ->with('pueblo')
                ->when($this->buscar, fn ($q) => $q->where('titulo', 'like', "%{$this->buscar}%"))
                ->orderBy('titulo')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
            'categoriasDisponibles' => Categoria::deGrupo('obra_literaria')->orderBy('nombre')->get(),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Literatura</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'obra-form')" wire:click="crear">
            Nueva obra
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por título..." />
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Autor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pueblo</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($obras as $obra)
                    <tr wire:key="obra-{{ $obra->id }}">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $obra->titulo }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $obra->autor }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $obra->tipo_obra ? self::TIPOS[$obra->tipo_obra] : '' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $obra->pueblo?->nombre }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3 whitespace-nowrap">
                            <x-boton-editar wire:click="editar({{ $obra->id }})" modal="obra-form" />
                            <x-boton-eliminar wire:click="confirmarEliminar({{ $obra->id }})" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">No hay obras literarias.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $obras->links() }}
        </div>
    </div>

    <x-modal name="obra-form" :show="$errors->isNotEmpty()" focusable maxWidth="xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $obraId ? 'Editar obra' : 'Nueva obra' }}
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="titulo" value="Título" />
                    <x-text-input wire:model="titulo" id="titulo" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="autor" value="Autor" />
                    <x-text-input wire:model="autor" id="autor" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('autor')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tipoObra" value="Tipo de obra" />
                    <select wire:model="tipoObra" id="tipoObra" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Selecciona un tipo</option>
                        @foreach (self::TIPOS as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('tipoObra')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="puebloId" value="Pueblo (opcional)" />
                    <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Sin pueblo asociado</option>
                        @foreach ($pueblos as $pueblo)
                            <option value="{{ $pueblo->id }}">{{ $pueblo->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('puebloId')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="anio" value="Año" />
                    <x-text-input wire:model="anio" id="anio" type="number" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('anio')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="paginas" value="Páginas" />
                    <x-text-input wire:model="paginas" id="paginas" type="number" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('paginas')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="archivo" value="Archivo (ruta o URL)" />
                    <x-text-input wire:model="archivo" id="archivo" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="portada" value="Portada (ruta o URL)" />
                    <x-text-input wire:model="portada" id="portada" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('portada')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
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

    <x-modal-confirmar-eliminar :id-a-eliminar="$idAEliminar" mensaje="¿Seguro que quieres eliminar esta obra?" />
</div>
