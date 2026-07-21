@php
    $enlaces = [];

    if (auth()->user()->can('redactar-noticias')) {
        $enlaces[] = ['ruta' => 'admin.dashboard', 'etiqueta' => 'Panel'];
        $enlaces[] = ['ruta' => 'admin.noticias', 'etiqueta' => 'Noticias'];
    }

    if (auth()->user()->can('administrar')) {
        $enlaces[] = ['ruta' => 'admin.pueblos', 'etiqueta' => 'Pueblos'];
        $enlaces[] = ['ruta' => 'admin.eventos', 'etiqueta' => 'Eventos'];
        $enlaces[] = ['ruta' => 'admin.categorias', 'etiqueta' => 'Categorías'];
        $enlaces[] = ['ruta' => 'admin.puntos-interes', 'etiqueta' => 'Puntos de interés'];
        $enlaces[] = ['ruta' => 'admin.servicios', 'etiqueta' => 'Servicios'];
        $enlaces[] = ['ruta' => 'admin.canciones', 'etiqueta' => 'Música'];
        $enlaces[] = ['ruta' => 'admin.obras-literarias', 'etiqueta' => 'Literatura'];
        $enlaces[] = ['ruta' => 'admin.usuarios', 'etiqueta' => 'Usuarios'];
    }
@endphp

<nav class="bg-white shadow-sm rounded-lg p-2 space-y-1">
    @foreach ($enlaces as $enlace)
        <a
            href="{{ route($enlace['ruta']) }}"
            wire:navigate
            class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs($enlace['ruta']) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            {{ $enlace['etiqueta'] }}
        </a>
    @endforeach
</nav>
