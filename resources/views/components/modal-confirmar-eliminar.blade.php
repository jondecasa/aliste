@props(['idAEliminar', 'mensaje' => '¿Seguro que quieres eliminar este registro?'])

<x-modal name="confirmar-eliminar" focusable maxWidth="sm">
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Eliminar</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $mensaje }} Esta acción no se puede deshacer.</p>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
            <button
                wire:click="eliminar({{ $idAEliminar }})"
                type="button"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
            >
                Eliminar
            </button>
        </div>
    </div>
</x-modal>
