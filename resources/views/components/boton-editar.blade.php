@props(['modal'])

<button
    {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition']) }}
    x-data=""
    x-on:click="$dispatch('open-modal', '{{ $modal }}')"
>
    Editar
</button>
