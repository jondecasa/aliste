<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('/', 'admin.dashboard')
        ->name('dashboard')
        ->middleware('can:redactar-noticias');

    Volt::route('noticias', 'admin.noticias')
        ->name('noticias')
        ->middleware('can:redactar-noticias');

    Route::middleware('can:administrar')->group(function () {
        Volt::route('pueblos', 'admin.pueblos')->name('pueblos');
        Volt::route('categorias', 'admin.categorias')->name('categorias');
        Volt::route('puntos-interes', 'admin.puntos-interes')->name('puntos-interes');
        Volt::route('servicios', 'admin.servicios')->name('servicios');
        Volt::route('blogs', 'admin.blogs')->name('blogs');
        Volt::route('canciones', 'admin.canciones')->name('canciones');
        Volt::route('obras-literarias', 'admin.obras-literarias')->name('obras-literarias');
        Volt::route('usuarios', 'admin.usuarios')->name('usuarios');
    });
});
