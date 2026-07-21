<?php

use App\Http\Controllers\Admin\EditorImagenController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('/', 'admin.dashboard')
        ->name('dashboard')
        ->middleware('can:redactar-noticias');

    Volt::route('noticias', 'admin.noticias')
        ->name('noticias')
        ->middleware('can:redactar-noticias');

    Volt::route('eventos', 'admin.eventos')
        ->name('eventos')
        ->middleware('can:gestionar-contenido-pueblo');

    Volt::route('puntos-interes', 'admin.puntos-interes')
        ->name('puntos-interes')
        ->middleware('can:gestionar-contenido-pueblo');

    Route::middleware('can:administrar')->group(function () {
        Route::post('editor/imagenes', [EditorImagenController::class, 'subir'])->name('editor.imagenes');

        Volt::route('banner', 'admin.banner')->name('banner');
        Volt::route('pueblos', 'admin.pueblos')->name('pueblos');
        Volt::route('categorias', 'admin.categorias')->name('categorias');
        Volt::route('servicios', 'admin.servicios')->name('servicios');
        Volt::route('canciones', 'admin.canciones')->name('canciones');
        Volt::route('obras-literarias', 'admin.obras-literarias')->name('obras-literarias');
        Volt::route('usuarios', 'admin.usuarios')->name('usuarios');
    });
});
