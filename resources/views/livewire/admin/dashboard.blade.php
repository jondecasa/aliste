<?php

use App\Models\Blog;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\ObraLiteraria;
use App\Models\Pueblo;
use App\Models\PuntoInteres;
use App\Models\Servicio;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public function with(): array
    {
        if (! auth()->user()->can('administrar')) {
            return [
                'contadores' => [
                    'Noticias' => Noticia::count(),
                ],
            ];
        }

        return [
            'contadores' => [
                'Pueblos' => Pueblo::count(),
                'Noticias' => Noticia::count(),
                'Categorías' => Categoria::count(),
                'Puntos de interés' => PuntoInteres::count(),
                'Servicios' => Servicio::count(),
                'Blogs' => Blog::count(),
                'Canciones' => Cancion::count(),
                'Obras literarias' => ObraLiteraria::count(),
            ],
        ];
    }
}; ?>

<div>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
        Panel de administración
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($contadores as $etiqueta => $total)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500">{{ $etiqueta }}</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $total }}</div>
            </div>
        @endforeach
    </div>
</div>
