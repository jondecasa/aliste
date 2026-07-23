@props(['modal'])

<button
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition']) }}
    x-data=""
    x-on:click="$dispatch('open-modal', '{{ $modal }}')"
    title="Editar"
>
    <span class="sr-only">Editar</span>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
        <path d="M17.414 2.586a2 2 0 0 0-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 0 0 0-2.828Z" />
        <path d="M2 4.5A1.5 1.5 0 0 1 3.5 3H10a.75.75 0 0 1 0 1.5H3.5a.5.5 0 0 0-.5.5v11a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5v-6.5a.75.75 0 0 1 1.5 0V15.5a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-11Z" />
    </svg>
</button>
