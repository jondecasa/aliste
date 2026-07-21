<?php

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

    public ?int $puebloId = null;
    public string $nombre = '';
    public ?string $descripcion = null;
    public ?string $contenidoHtml = null;
    public ?string $portadaActual = null;
    public $foto = null;
    public ?int $poblacion = null;
    public ?int $altitud = null;
    public ?float $latitud = null;
    public ?float $longitud = null;
    public bool $esCabecera = false;
    public ?int $idAEliminar = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function crear(): void
    {
        $this->resetearFormulario();
        $this->dispatch('open-modal', 'pueblo-form');
    }

    public function editar(int $id): void
    {
        $pueblo = Pueblo::findOrFail($id);

        $this->puebloId = $pueblo->id;
        $this->nombre = $pueblo->nombre;
        $this->descripcion = $pueblo->descripcion;
        $this->contenidoHtml = $pueblo->contenido_html;
        $this->portadaActual = $pueblo->portada;
        $this->foto = null;
        $this->poblacion = $pueblo->poblacion;
        $this->altitud = $pueblo->altitud;
        $this->latitud = $pueblo->latitud;
        $this->longitud = $pueblo->longitud;
        $this->esCabecera = $pueblo->es_cabecera;

        $this->dispatch('open-modal', 'pueblo-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'contenidoHtml' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'poblacion' => ['nullable', 'integer', 'min:0'],
            'altitud' => ['nullable', 'integer', 'min:0'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'esCabecera' => ['boolean'],
        ]);

        $rutaPortada = $this->portadaActual;

        if ($this->foto) {
            if ($this->portadaActual) {
                Storage::disk('public')->delete($this->portadaActual);
            }

            $rutaPortada = $this->foto->store('pueblos', 'public');
        }

        Pueblo::updateOrCreate(
            ['id' => $this->puebloId],
            [
                'nombre' => $datos['nombre'],
                'slug' => Str::slug($datos['nombre']),
                'descripcion' => $datos['descripcion'],
                'contenido_html' => $datos['contenidoHtml'],
                'portada' => $rutaPortada,
                'poblacion' => $datos['poblacion'],
                'altitud' => $datos['altitud'],
                'latitud' => $datos['latitud'],
                'longitud' => $datos['longitud'],
                'es_cabecera' => $datos['esCabecera'],
            ]
        );

        $this->dispatch('close-modal', 'pueblo-form');
        $this->resetearFormulario();
    }

    public function confirmarEliminar(int $id): void
    {
        $this->idAEliminar = $id;
    }

    public function eliminar(int $id): void
    {
        $pueblo = Pueblo::findOrFail($id);

        if ($pueblo->portada) {
            Storage::disk('public')->delete($pueblo->portada);
        }

        $pueblo->delete();

        $this->idAEliminar = null;
        $this->dispatch('close-modal', 'confirmar-eliminar');
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'puebloId', 'nombre', 'descripcion', 'contenidoHtml', 'portadaActual', 'foto',
            'poblacion', 'altitud', 'latitud', 'longitud', 'esCabecera',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'pueblos' => Pueblo::query()
                ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
                ->orderBy('nombre')
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pueblos</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'pueblo-form')" wire:click="crear">
            Nuevo pueblo
        </x-primary-button>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por nombre..." />
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Población</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cabecera</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($pueblos as $pueblo)
                    <tr wire:key="pueblo-{{ $pueblo->id }}">
                        <td class="px-6 py-4">
                            @if ($pueblo->portada_url)
                                <img src="{{ $pueblo->portada_url }}" alt="{{ $pueblo->nombre }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100"></div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $pueblo->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $pueblo->poblacion ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $pueblo->es_cabecera ? 'Sí' : '' }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <button
                                wire:click="editar({{ $pueblo->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'pueblo-form')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >Editar</button>
                            <button
                                wire:click="confirmarEliminar({{ $pueblo->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'confirmar-eliminar')"
                                class="text-red-600 hover:text-red-900"
                            >Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No hay pueblos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $pueblos->links() }}
        </div>
    </div>

    <x-modal name="pueblo-form" :show="$errors->isNotEmpty()" focusable maxWidth="2xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $puebloId ? 'Editar pueblo' : 'Nuevo pueblo' }}
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input wire:model="nombre" id="nombre" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="foto" value="Foto del pueblo" />

                    @if ($foto)
                        <img src="{{ $foto->temporaryUrl() }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @elseif ($portadaActual)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($portadaActual) }}" class="mt-2 w-32 h-32 object-cover rounded-lg">
                    @endif

                    <input wire:model="foto" id="foto" type="file" accept="image/*" class="mt-2 block w-full text-sm" />
                    <div wire:loading wire:target="foto" class="text-xs text-gray-500 mt-1">Subiendo imagen...</div>
                    <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="poblacion" value="Población" />
                    <x-text-input wire:model="poblacion" id="poblacion" type="number" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('poblacion')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="altitud" value="Altitud (m)" />
                    <x-text-input wire:model="altitud" id="altitud" type="number" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('altitud')" class="mt-2" />
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
                    <label class="inline-flex items-center">
                        <input wire:model="esCabecera" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2 text-sm text-gray-600">Es cabecera de comarca</span>
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <x-input-label value="Página personalizada del pueblo" />

                    <div
                        wire:ignore
                        x-data
                        x-init="
                            window.tinymce.init({
                                selector: '#contenidoHtml',
                                base_url: '{{ asset('vendor/tinymce') }}',
                                suffix: '.min',
                                license_key: 'gpl',
                                height: 420,
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
                                    editor.on('change input undo redo', () => {
                                        $wire.set('contenidoHtml', editor.getContent());
                                    });

                                    window.addEventListener('open-modal', (evento) => {
                                        if (evento.detail === 'pueblo-form') {
                                            editor.setContent($wire.contenidoHtml || '');
                                        }
                                    });
                                },
                            });
                        "
                    >
                        <textarea id="contenidoHtml">{{ $contenidoHtml }}</textarea>
                    </div>

                    <p class="mt-1 text-xs text-gray-500">Este contenido se mostrará tal cual en la página pública del pueblo (/pueblos/{{ Illuminate\Support\Str::slug($nombre) ?: 'slug' }}).</p>
                    <x-input-error :messages="$errors->get('contenidoHtml')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Guardar</x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal-confirmar-eliminar :id-a-eliminar="$idAEliminar" mensaje="¿Seguro que quieres eliminar este pueblo?" />
</div>
