<?php

use App\Models\AudioCancion;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Pueblo;
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

    public ?int $cancionId = null;
    public ?int $puebloId = null;
    public string $titulo = '';
    public ?string $artista = null;
    public ?string $album = null;
    public ?int $duracion = null;
    public ?int $anio = null;
    public ?string $portadaActual = null;
    public $nuevaPortada = null;
    public ?string $descripcion = null;
    public ?string $letra = null;

    /** @var array<int, int> */
    public array $categoriaIds = [];

    /** @var array<int, array{id: int, titulo: ?string, url: string}> */
    public array $audiosExistentes = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $nuevosAudios = [];

    /**
     * Ligada al input de fichero. Cada vez que se seleccionan archivos se
     * acumulan en $nuevosAudios y esta propiedad se vacía, porque el propio
     * input de fichero SUSTITUYE su selección cada vez que se usa (si no se
     * acumulara aquí, elegir un segundo audio borraría el primero).
     *
     * @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $nuevaSeleccionAudios = [];

    public ?int $idAEliminar = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function updatedNuevaSeleccionAudios(): void
    {
        foreach ($this->nuevaSeleccionAudios as $archivo) {
            $this->nuevosAudios[] = $archivo;
        }

        $this->nuevaSeleccionAudios = [];
    }

    public function eliminarNuevoAudio(int $indice): void
    {
        unset($this->nuevosAudios[$indice]);
        $this->nuevosAudios = array_values($this->nuevosAudios);
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
        $this->duracion = $cancion->duracion;
        $this->anio = $cancion->anio;
        $this->portadaActual = $cancion->portada;
        $this->nuevaPortada = null;
        $this->descripcion = $cancion->descripcion;
        $this->letra = $cancion->letra;
        $this->categoriaIds = $cancion->categorias()->pluck('categorias.id')->all();
        $this->cargarAudios();

        $this->dispatch('open-modal', 'cancion-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'puebloId' => ['nullable', 'exists:pueblos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'artista' => ['nullable', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'duracion' => ['nullable', 'integer', 'min:0'],
            'anio' => ['nullable', 'integer', 'min:0', 'max:'.(date('Y') + 1)],
            'nuevaPortada' => ['nullable', 'image', 'max:4096'],
            'descripcion' => ['nullable', 'string'],
            'letra' => ['nullable', 'string'],
            'categoriaIds' => ['array'],
            'categoriaIds.*' => ['exists:categorias,id'],
            'audiosExistentes' => ['array'],
            'audiosExistentes.*.id' => ['integer', 'exists:audios_cancion,id'],
            'audiosExistentes.*.titulo' => ['nullable', 'string', 'max:255'],
            'nuevosAudios' => ['array'],
            'nuevosAudios.*' => ['file', 'mimes:mp3,wav,ogg,m4a,aac,flac', 'max:20480'],
        ]);

        $rutaPortada = $this->portadaActual;

        if ($this->nuevaPortada) {
            if ($this->portadaActual) {
                Storage::disk('public')->delete($this->portadaActual);
            }

            $rutaPortada = OptimizadorImagenes::guardar($this->nuevaPortada, 'canciones/portadas');
        }

        $cancion = Cancion::updateOrCreate(
            ['id' => $this->cancionId],
            [
                'pueblo_id' => $datos['puebloId'],
                'titulo' => $datos['titulo'],
                'slug' => Str::slug($datos['titulo']),
                'artista' => $datos['artista'],
                'album' => $datos['album'],
                'duracion' => $datos['duracion'],
                'anio' => $datos['anio'],
                'portada' => $rutaPortada,
                'descripcion' => $datos['descripcion'],
                'letra' => $datos['letra'],
            ]
        );

        $cancion->categorias()->sync($datos['categoriaIds']);

        foreach ($datos['audiosExistentes'] ?? [] as $item) {
            AudioCancion::where('id', $item['id'])
                ->where('cancion_id', $cancion->id)
                ->update(['titulo' => $item['titulo']]);
        }

        $orden = (int) $cancion->audios()->max('orden');

        foreach ($this->nuevosAudios as $archivo) {
            $orden++;

            AudioCancion::create([
                'cancion_id' => $cancion->id,
                'archivo' => $archivo->store('canciones/audios', 'public'),
                'titulo' => pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME),
                'orden' => $orden,
            ]);
        }

        $this->dispatch('close-modal', 'cancion-form');
        $this->resetearFormulario();
    }

    public function eliminarAudio(int $audioId): void
    {
        $audio = AudioCancion::findOrFail($audioId);

        abort_unless($this->cancionId && $audio->cancion_id === $this->cancionId, 403);

        Storage::disk('public')->delete($audio->archivo);
        $audio->delete();

        $this->cargarAudios();
    }

    public function confirmarEliminar(int $id): void
    {
        $this->idAEliminar = $id;
    }

    public function eliminar(int $id): void
    {
        $cancion = Cancion::findOrFail($id);

        if ($cancion->portada) {
            Storage::disk('public')->delete($cancion->portada);
        }

        foreach ($cancion->audios as $audio) {
            Storage::disk('public')->delete($audio->archivo);
        }

        $cancion->delete();

        $this->idAEliminar = null;
        $this->dispatch('close-modal', 'confirmar-eliminar');
    }

    private function cargarAudios(): void
    {
        $this->audiosExistentes = $this->cancionId
            ? Cancion::findOrFail($this->cancionId)->audios->map(fn (AudioCancion $audio) => [
                'id' => $audio->id,
                'titulo' => $audio->titulo,
                'url' => $audio->archivo_url,
            ])->all()
            : [];
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'cancionId', 'puebloId', 'titulo', 'artista', 'album',
            'duracion', 'anio', 'portadaActual', 'nuevaPortada', 'descripcion', 'letra',
            'categoriaIds', 'audiosExistentes', 'nuevosAudios', 'nuevaSeleccionAudios',
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
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Música</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'cancion-form')" wire:click="crear">
            Nueva canción
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por título..." />
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Título</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Artista</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pueblo</th>
                        <th class="px-6 py-3 sticky right-0 bg-gray-50 dark:bg-gray-700/50"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($canciones as $cancion)
                        <tr wire:key="cancion-{{ $cancion->id }}">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $cancion->titulo }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $cancion->artista }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $cancion->pueblo?->nombre }}</td>
                            <td class="px-6 py-4 text-right text-sm space-x-3 whitespace-nowrap sticky right-0 bg-white dark:bg-gray-800">
                                <x-boton-editar wire:click="editar({{ $cancion->id }})" modal="cancion-form" />
                                <x-boton-eliminar wire:click="confirmarEliminar({{ $cancion->id }})" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">No hay canciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $canciones->links() }}
        </div>
    </div>

    <x-modal name="cancion-form" :show="$errors->isNotEmpty()" focusable maxWidth="2xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
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
                    <x-input-label for="duracion" value="Duración (segundos)" />
                    <x-text-input wire:model="duracion" id="duracion" type="number" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('duracion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="nuevaPortada" value="Portada" />

                    @if ($nuevaPortada)
                        <img src="{{ $nuevaPortada->temporaryUrl() }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @elseif ($portadaActual)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($portadaActual) }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @endif

                    <input wire:model="nuevaPortada" id="nuevaPortada" type="file" accept="image/*" class="mt-2 block w-full text-sm" />
                    <div wire:loading wire:target="nuevaPortada" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Subiendo imagen...</div>
                    <x-input-error :messages="$errors->get('nuevaPortada')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Archivos de audio" />

                    @if (count($audiosExistentes))
                        <div class="mt-2 space-y-2">
                            @foreach ($audiosExistentes as $index => $audio)
                                <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 rounded-md p-2" wire:key="audio-existente-{{ $audio['id'] }}">
                                    <audio controls preload="none" src="{{ $audio['url'] }}" class="h-8 flex-shrink-0 max-w-[160px]"></audio>
                                    <input
                                        type="text"
                                        wire:model="audiosExistentes.{{ $index }}.titulo"
                                        placeholder="Título de la pista (opcional)"
                                        class="flex-1 min-w-0 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm"
                                    >
                                    <button
                                        type="button"
                                        wire:click="eliminarAudio({{ $audio['id'] }})"
                                        wire:confirm="¿Eliminar este archivo de audio?"
                                        class="flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        title="Eliminar audio"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (count($nuevosAudios))
                        <div class="mt-2 space-y-2">
                            @foreach ($nuevosAudios as $index => $archivo)
                                <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/20 rounded-md p-2" wire:key="audio-nuevo-{{ $index }}">
                                    <audio controls preload="none" src="{{ $archivo->temporaryUrl() }}" class="h-8 flex-shrink-0 max-w-[160px]"></audio>
                                    <span class="flex-1 min-w-0 text-sm text-gray-600 dark:text-gray-300 truncate">{{ $archivo->getClientOriginalName() }}</span>
                                    <span class="flex-shrink-0 text-[11px] text-amber-700 dark:text-amber-400 font-semibold uppercase">Nuevo</span>
                                    <button
                                        type="button"
                                        wire:click="eliminarNuevoAudio({{ $index }})"
                                        class="flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        title="Quitar de la selección"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <input wire:model="nuevaSeleccionAudios" type="file" multiple accept="audio/*" class="mt-2 block w-full text-sm" />
                    <div wire:loading wire:target="nuevaSeleccionAudios" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Subiendo audio...</div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Puedes seleccionar varios archivos a la vez, o repetir la selección para ir añadiendo más (mp3, wav, ogg, m4a...). Se guardarán al pulsar "Guardar".</p>
                    <x-input-error :messages="$errors->get('nuevosAudios')" class="mt-2" />
                    <x-input-error :messages="$errors->get('nuevosAudios.*')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="letra" value="Letra de la canción" />
                    <textarea wire:model="letra" id="letra" rows="8" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm"></textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Escribe cada verso en su propia línea. En la página de la canción se mostrará con un formato distintivo.</p>
                    <x-input-error :messages="$errors->get('letra')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Descripción" />

                    <div
                        wire:ignore
                        x-init="
                            $watch('show', (visible) => {
                                if (! visible) {
                                    window.tinymce.get('descripcion')?.remove();
                                }
                            });

                            window.addEventListener('open-modal', (evento) => {
                                const nombreModal = Array.isArray(evento.detail) ? evento.detail[0] : evento.detail;

                                if (nombreModal !== 'cancion-form') {
                                    return;
                                }

                                window.tinymce.get('descripcion')?.remove();

                                window.tinymce.init({
                                    selector: '#descripcion',
                                    base_url: '{{ asset('vendor/tinymce') }}',
                                    suffix: '.min',
                                    license_key: 'gpl',
                                    height: 300,
                                    menubar: false,
                                    branding: false,
                                    plugins: 'advlist autolink lists link image table code media fullscreen',
                                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media table | code fullscreen',
                                    images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
                                        const datosFormulario = new FormData();
                                        datosFormulario.append('file', blobInfo.blob(), blobInfo.filename());

                                        fetch('{{ route('admin.editor.imagenes') }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json',
                                            },
                                            body: datosFormulario,
                                        })
                                            .then((respuesta) => respuesta.ok ? respuesta.json() : Promise.reject())
                                            .then((datos) => resolve(datos.location))
                                            .catch(() => reject('Error al subir la imagen'));
                                    }),
                                    setup: (editor) => {
                                        editor.on('init', () => {
                                            editor.setContent($wire.descripcion || '');
                                        });

                                        editor.on('change input undo redo', () => {
                                            $wire.set('descripcion', editor.getContent());
                                        });
                                    },
                                });
                            });
                        "
                    >
                        <textarea id="descripcion">{{ $descripcion }}</textarea>
                    </div>
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

    <x-modal-confirmar-eliminar :id-a-eliminar="$idAEliminar" mensaje="¿Seguro que quieres eliminar esta canción? También se eliminarán sus archivos de audio." />
</div>
