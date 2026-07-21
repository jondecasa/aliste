<?php

use App\Models\Blog;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public ?int $blogId = null;
    public string $nombre = '';
    public ?string $url = null;
    public bool $esExterno = true;
    public ?string $descripcion = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function crear(): void
    {
        $this->resetearFormulario();
        $this->dispatch('open-modal', 'blog-form');
    }

    public function editar(int $id): void
    {
        $blog = Blog::findOrFail($id);

        $this->blogId = $blog->id;
        $this->nombre = $blog->nombre;
        $this->url = $blog->url;
        $this->esExterno = $blog->es_externo;
        $this->descripcion = $blog->descripcion;

        $this->dispatch('open-modal', 'blog-form');
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'esExterno' => ['boolean'],
            'descripcion' => ['nullable', 'string'],
        ]);

        Blog::updateOrCreate(
            ['id' => $this->blogId],
            [
                'nombre' => $datos['nombre'],
                'slug' => Str::slug($datos['nombre']),
                'url' => $datos['url'],
                'es_externo' => $datos['esExterno'],
                'descripcion' => $datos['descripcion'],
            ]
        );

        $this->dispatch('close-modal', 'blog-form');
        $this->resetearFormulario();
    }

    public function eliminar(int $id): void
    {
        Blog::findOrFail($id)->delete();
    }

    private function resetearFormulario(): void
    {
        $this->reset(['blogId', 'nombre', 'url', 'descripcion']);
        $this->esExterno = true;
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'blogs' => Blog::query()
                ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
                ->orderBy('nombre')
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Blogs</h2>

        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'blog-form')" wire:click="crear">
            Nuevo blog
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($blogs as $blog)
                    <tr wire:key="blog-{{ $blog->id }}">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $blog->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $blog->es_externo ? 'Externo' : 'Interno' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">{{ $blog->url }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <button
                                wire:click="editar({{ $blog->id }})"
                                x-data=""
                                x-on:click="$dispatch('open-modal', 'blog-form')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >Editar</button>
                            <button
                                wire:click="eliminar({{ $blog->id }})"
                                wire:confirm="¿Seguro que quieres eliminar este blog?"
                                class="text-red-600 hover:text-red-900"
                            >Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No hay blogs.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $blogs->links() }}
        </div>
    </div>

    <x-modal name="blog-form" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="guardar" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ $blogId ? 'Editar blog' : 'Nuevo blog' }}
            </h2>

            <div class="mt-6 space-y-4">
                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input wire:model="nombre" id="nombre" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="url" value="URL" />
                    <x-text-input wire:model="url" id="url" type="text" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('url')" class="mt-2" />
                </div>

                <div>
                    <label class="inline-flex items-center">
                        <input wire:model="esExterno" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2 text-sm text-gray-600">Blog externo</span>
                    </label>
                </div>

                <div>
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea wire:model="descripcion" id="descripcion" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Guardar</x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
