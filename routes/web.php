<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'public.home')->name('inicio');
Volt::route('pueblos', 'public.pueblos')->name('pueblos');
Volt::route('servicios', 'public.servicios')->name('servicios');
Volt::route('blog', 'public.blog')->name('blog');

Route::view('panel', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('perfil', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
