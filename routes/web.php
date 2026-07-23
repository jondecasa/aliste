<?php

use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'public.home')->name('inicio');
Volt::route('pueblos', 'public.pueblos')->name('pueblos');
Volt::route('pueblos/{pueblo}', 'public.pueblo')->name('pueblo');
Volt::route('pueblos/{pueblo}/calendario', 'public.calendario')->name('pueblo.calendario');
Volt::route('pueblos/{pueblo}/gente', 'public.gente')->name('pueblo.gente');
Volt::route('servicios', 'public.servicios')->name('servicios');
Volt::route('musica', 'public.musica')->name('musica');
Volt::route('musica/{cancion}', 'public.cancion')->name('cancion');
Volt::route('noticias', 'public.blog')->name('noticias');
Volt::route('noticias/{noticia}', 'public.noticia')->name('noticia');
Volt::route('contacto', 'public.contacto')->name('contacto');
Volt::route('buscar', 'public.buscar')->name('buscar');
Volt::route('politica-cookies', 'public.cookies')->name('cookies');
Volt::route('politica-privacidad', 'public.privacidad')->name('privacidad');

Route::view('panel', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('perfil', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::post('push/suscribirse', [PushSubscriptionController::class, 'store'])->name('push.suscribirse');
    Route::post('push/desuscribirse', [PushSubscriptionController::class, 'destroy'])->name('push.desuscribirse');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
