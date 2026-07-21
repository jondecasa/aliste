<?php

use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Pueblo;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public ?int $cancionId = null;
    public ?int $puebloId = null;
    public string $titulo = '';
    public ?string $artista = null;
    public ?string $album = null;
    public ?string $archivoAudio = null;
    public ?int $duracion = null;
    public ?int $anio = null;
    public ?string $portada = null;
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
        $this->dispatch('open-modal', 'cancion-form');
    }

    public function editar(int $id): void
    {
        $cancion = Cancion::findOrFail($id);

        $this->cancionId = $cancion->id;
        $this->puebloId = $cancion->pueblo_id;
        $this->titulo = $cancion->titulo;
        $this->artista = $cancion->artista;
        $this->album = $cancion->album;
        $this->archivoAudio = $cancion->archivo_audio;
        $this->duracion = $cancion->duracion;
        $this->anio = $cancion->anio;
        $this->portada = $cancion->portada;
        $this->descripcion = $cancion->descripcion;
        $this->categoriaIds = $cancion->categorias()->pluck('categorias.id')->all();

        $this->dispatch('open-modal', 'cancion-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'puebloId' => ['nullable', 'exists:pueblos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'artista' => ['nullable', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'archivoAudio' => ['nullable', 'string', 'max:255'],
            'duracion' => ['nullable', 'integer', 'min:0'],
            'anio' => ['nullable', 'integer', 'min:0', 'max:'.(date('Y') + 1)],
            'portada' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'categoriaIds' => ['array'],
            'categoriaIds.*' => ['exists:categorias,id'],
        ]);

        $cancion = Cancion::updateOrCreate(
            ['id' => $this->cancionId],
            [
                'pueblo_id' => $datos['puebloId'],
                'titulo' => $datos['titulo'],
                'slug' => Str::slug($datos['titulo']),
                'artista' => $datos['artista'],
                'album' => $datos['album'],
                'archivo_audio' => $datos['archivoAudio'],
                'duracion' => $datos['duracion'],
                'anio' => $datos['anio'],
                'portada' => $datos['portada'],
                'descripcion' => $datos['descripcion'],
            ]
        );

        $cancion->categorias()->sync($datos['categoriaIds']);

        $this->dispatch('close-modal', 'cancion-form');
        $this->resetearFormulario();
    }

    public function eliminar(int $id): void
    {
        Cancion::findOrFail($id)->delete();
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'cancionId', 'puebloId', 'titulo', 'artista', 'album',
            'archivoAudio', 'duracion', 'anio', 'portada', 'descripcion', 'categoriaIds',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'canciones' => Cancion::query()
                ->with('pueblo')
                ->when($this->buscar, fn ($q) => $q->where('titulo', 'like', "%{$this->buscar}%"))
                ->orderBy('titulo')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
            'categoriasDisponibles' => Categoria::deGrupo('cancion')->orderBy('nombre')->get(),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Música</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'cancion-form')" wire:click="crear">
            Nueva canción
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por título..." />
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Artista</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pueblo</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($canciones as $cancion)
                    <tr wire:key="cancion-{{ $cancion->id }}">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $cancion->titulo }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cancion->artista }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cancion->pueblo?->nombre }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <button
                                wire:click="editar({{ $cancion->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'cancion-form')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >Editar</button>
                            <button
                                wire:click="eliminar({{ $cancion->id }})"
                                wire:confirm="¿Seguro que quieres eliminar esta canción?"
                                class="text-red-600 hover:text-red-900"
                            >Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No hay canciones.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $canciones->links() }}
        </div>
    </div>

    <x-modal name="cancion-form" :show="$errors->isNotEmpty()" focusable maxWidth="xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $cancionId ? 'Editar canción' : 'Nueva canción' }}
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="titulo" value="Título" />
                    <x-text-input wire:model="titulo" id="titulo" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="artista" value="Artista" />
                    <x-text-input wire:model="artista" id="artista" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('artista')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="album" value="Álbum" />
                    <x-text-input wire:model="album" id="album" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('album')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="puebloId" value="Pueblo (opcional)" />
                    <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                    <x-input-label for="duracion" value="Duración (segundos)" />
                    <x-text-input wire:model="duracion" id="duracion" type="number" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('duracion')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="archivoAudio" value="Archivo de audio (ruta o URL)" />
                    <x-text-input wire:model="archivoAudio" id="archivoAudio" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('archivoAudio')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="portada" value="Portada (ruta o URL)" />
                    <x-text-input wire:model="portada" id="portada" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('portada')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Categorías" />
                    <div class="mt-1 grid grid-cols-2 sm:grid-cols-3 gap-2">
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
