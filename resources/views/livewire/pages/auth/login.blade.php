<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth-split')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('profile', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="font-serif text-[28px] text-tinta sm:text-3xl">Bienvenido de vuelta</h1>
    <p class="mb-7 mt-2 text-sm text-tinta-muted">Entra para gestionar tu perfil y tus notificaciones.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-tinta">Correo electrónico</label>
            <input
                wire:model="form.email"
                id="email"
                type="email"
                required
                autofocus
                autocomplete="username"
                class="block h-[46px] w-full rounded-lg border-tinta-borde bg-white px-4 text-sm text-tinta placeholder:text-tinta-muted focus:border-terracota focus:ring-terracota"
            >
            @error('form.email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-semibold text-tinta">Contraseña</label>
            <input
                wire:model="form.password"
                id="password"
                type="password"
                required
                autocomplete="current-password"
                class="block h-[46px] w-full rounded-lg border-tinta-borde bg-white px-4 text-sm text-tinta placeholder:text-tinta-muted focus:border-terracota focus:ring-terracota"
            >
            @error('form.password') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-tinta-muted">
                <input wire:model="form.remember" type="checkbox" class="rounded border-tinta-borde text-terracota focus:ring-terracota">
                Recuérdame
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-terracota">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <button type="submit" class="mt-1 w-full rounded-full bg-terracota py-3.5 text-sm font-bold text-white transition hover:bg-terracota-dark">
            Entrar
        </button>
    </form>

    <div class="my-6 flex items-center gap-3">
        <div class="h-px flex-1 bg-tinta-borde"></div>
        <span class="text-xs text-tinta-muted">o</span>
        <div class="h-px flex-1 bg-tinta-borde"></div>
    </div>

    <a href="{{ route('auth.google.redirigir') }}"
        class="flex w-full items-center justify-center gap-2.5 rounded-full border border-tinta-borde bg-white py-3 text-sm font-semibold text-tinta transition hover:bg-crema">
        <svg class="h-[18px] w-[18px]" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z" />
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z" />
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z" />
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z" />
        </svg>
        Continuar con Google
    </a>

    <p class="mt-6 text-center text-sm text-tinta-muted">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-terracota">Regístrate</a>
    </p>
</div>
