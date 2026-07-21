<?php

use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\Pueblo;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public ?int $noticiaId = null;
    public ?int $puebloId = null;
    public string $titulo = '';
    public ?string $extracto = null;
    public ?string $cuerpo = null;
    public ?string $fuenteNombre = null;
    public ?string $fuenteUrl = null;
    public ?string $urlExterna = null;
    public ?string $imagenPortada = null;
    public ?string $publicadoEn = null;

    /** @var array<int, int> */
    public array $categoriaIds = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('redactar-noticias'), 403);
    }

    public function crear(): void
    {
        $this->resetearFormulario();
        $this->dispatch('open-modal', 'noticia-form');
    }

    public function editar(int $id): void
    {
        $noticia = Noticia::findOrFail($id);

        $this->noticiaId = $noticia->id;
        $this->puebloId = $noticia->pueblo_id;
        $this->titulo = $noticia->titulo;
        $this->extracto = $noticia->extracto;
        $this->cuerpo = $noticia->cuerpo;
        $this->fuenteNombre = $noticia->fuente_nombre;
        $this->fuenteUrl = $noticia->fuente_url;
        $this->urlExterna = $noticia->url_externa;
        $this->imagenPortada = $noticia->imagen_portada;
        $this->publicadoEn = $noticia->publicado_en?->format('Y-m-d\TH:i');
        $this->categoriaIds = $noticia->categorias()->pluck('categorias.id')->all();

        $this->dispatch('open-modal', 'noticia-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'puebloId' => ['nullable', 'exists:pueblos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'extracto' => ['nullable', 'string'],
            'cuerpo' => ['nullable', 'string'],
            'fuenteNombre' => ['nullable', 'string', 'max:255'],
            'fuenteUrl' => ['nullable', 'url', 'max:255'],
            'urlExterna' => ['nullable', 'url', 'max:255'],
            'imagenPortada' => ['nullable', 'string', 'max:255'],
            'publicadoEn' => ['nullable', 'date'],
            'categoriaIds' => ['required', 'array', 'min:1'],
            'categoriaIds.*' => ['exists:categorias,id'],
        ], [
            'categoriaIds.required' => 'Selecciona al menos una categoría.',
            'categoriaIds.min' => 'Selecciona al menos una categoría.',
        ]);

        $noticia = Noticia::updateOrCreate(
            ['id' => $this->noticiaId],
            [
                'pueblo_id' => $datos['puebloId'],
                'titulo' => $datos['titulo'],
                'slug' => Str::slug($datos['titulo']),
                'extracto' => $datos['extracto'],
                'cuerpo' => $datos['cuerpo'],
                'fuente_nombre' => $datos['fuenteNombre'],
                'fuente_url' => $datos['fuenteUrl'],
                'url_externa' => $datos['urlExterna'],
                'imagen_portada' => $datos['imagenPortada'],
                'publicado_en' => $datos['publicadoEn'],
            ]
        );

        $noticia->categorias()->sync($datos['categoriaIds']);

        $this->dispatch('close-modal', 'noticia-form');
        $this->resetearFormulario();
    }

    public function eliminar(int $id): void
    {
        Noticia::findOrFail($id)->delete();
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'noticiaId', 'puebloId', 'titulo', 'extracto', 'cuerpo',
            'fuenteNombre', 'fuenteUrl', 'urlExterna', 'imagenPortada',
            'publicadoEn', 'categoriaIds',
        ]);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'noticias' => Noticia::query()
                ->with('pueblo')
                ->when($this->buscar, fn ($q) => $q->where('titulo', 'like', "%{$this->buscar}%"))
                ->orderByDesc('publicado_en')
                ->paginate(15),
            'pueblos' => Pueblo::orderBy('nombre')->get(),
            'categoriasDisponibles' => Categoria::deGrupo('noticia')->orderBy('nombre')->get(),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Noticias</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'noticia-form')" wire:click="crear">
            Nueva noticia
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pueblo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Publicado</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($noticias as $noticia)
                    <tr wire:key="noticia-{{ $noticia->id }}">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $noticia->titulo }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $noticia->pueblo?->nombre ?? 'Comarca' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $noticia->publicado_en?->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <button
                                wire:click="editar({{ $noticia->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'noticia-form')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >Editar</button>
                            <button
                                wire:click="eliminar({{ $noticia->id }})"
                                wire:confirm="¿Seguro que quieres eliminar esta noticia?"
                                class="text-red-600 hover:text-red-900"
                            >Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No hay noticias.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $noticias->links() }}
        </div>
    </div>

    <x-modal name="noticia-form" :show="$errors->isNotEmpty()" focusable maxWidth="xl">
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $noticiaId ? 'Editar noticia' : 'Nueva noticia' }}
            </h2>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="titulo" value="Título" />
                    <x-text-input wire:model="titulo" id="titulo" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="puebloId" value="Pueblo (opcional)" />
                    <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Toda la comarca</option>
                        @foreach ($pueblos as $pueblo)
                            <option value="{{ $pueblo->id }}">{{ $pueblo->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('puebloId')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="publicadoEn" value="Fecha de publicación" />
                    <x-text-input wire:model="publicadoEn" id="publicadoEn" type="datetime-local" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('publicadoEn')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="extracto" value="Extracto" />
                    <textarea wire:model="extracto" id="extracto" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('extracto')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="cuerpo" value="Cuerpo" />
                    <textarea wire:model="cuerpo" id="cuerpo" rows="6" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('cuerpo')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="fuenteNombre" value="Nombre de la fuente" />
                    <x-text-input wire:model="fuenteNombre" id="fuenteNombre" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('fuenteNombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="fuenteUrl" value="URL de la fuente" />
                    <x-text-input wire:model="fuenteUrl" id="fuenteUrl" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('fuenteUrl')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="urlExterna" value="URL del artículo original" />
                    <x-text-input wire:model="urlExterna" id="urlExterna" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('urlExterna')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="imagenPortada" value="Imagen de portada (ruta o URL)" />
                    <x-text-input wire:model="imagenPortada" id="imagenPortada" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('imagenPortada')" class="mt-2" />
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
