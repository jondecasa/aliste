<?php

use App\Models\Banner;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public ?string $contenidoHtml = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);

        $this->contenidoHtml = Banner::obtener()->contenido_html;
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'contenidoHtml' => ['nullable', 'string'],
        ]);

        Banner::obtener()->update(['contenido_html' => $datos['contenidoHtml']]);

        $this->dispatch('banner-guardado');
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Banner de la home</h2>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        Este bloque se muestra en la página de inicio, entre la cabecera y el calendario de la comarca. Si lo dejas vacío, no se mostrará nada.
    </p>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <form wire:submit="guardar">
            <div
                wire:ignore
                x-data
                x-init="
                    window.tinymce.get('contenidoHtml')?.remove();
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
                        },
                    });
                "
            >
                <textarea id="contenidoHtml">{{ $contenidoHtml }}</textarea>
            </div>

            <x-input-error :messages="$errors->get('contenidoHtml')" class="mt-2" />

            <div class="mt-6 flex items-center gap-4">
                <x-primary-button>Guardar</x-primary-button>
                <x-action-message class="me-3" on="banner-guardado">Guardado.</x-action-message>
            </div>
        </form>
    </div>
</div>
