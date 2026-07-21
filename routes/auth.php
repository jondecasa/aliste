<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('registro', 'pages.auth.register')
        ->name('register');

    Volt::route('iniciar-sesion', 'pages.auth.login')
        ->name('login');

    Volt::route('olvide-contrasena', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('restablecer-contrasena/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

    Route::get('auth/google/redirigir', [GoogleAuthController::class, 'redirect'])
        ->name('auth.google.redirigir');

    Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Volt::route('verificar-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verificar-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirmar-contrasena', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
