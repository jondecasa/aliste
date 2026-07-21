@props(['modal' => 'confirmar-eliminar'])

<button
    {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1.5 rounded-md bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition']) }}
    x-data=""
    x-on:click="$dispatch('open-modal', '{{ $modal }}')"
>
    Eliminar
</button>
