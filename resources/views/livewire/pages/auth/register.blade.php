<?php

use App\Livewire\Concerns\VerificaCaptcha;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    use VerificaCaptcha;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $captchaToken = '';

    /**
     * Campo señuelo (honeypot): invisible para una persona, pero muchos bots
     * lo rellenan igualmente. Si llega con contenido, se descarta el registro.
     */
    public string $sitioWeb = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->ensureIsNotRateLimited();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'captchaToken' => [config('services.recaptcha.site_key') ? 'required' : 'nullable', 'string'],
        ], [
            'captchaToken.required' => 'Por favor, confirma el captcha antes de continuar.',
        ]);

        if ($this->sitioWeb !== '' || ! $this->verificarCaptcha($validated['captchaToken'] ?? '')) {
            RateLimiter::hit($this->throttleKey(), 3600);

            $this->addError('captchaToken', 'No hemos podido verificar el captcha. Inténtalo de nuevo.');
            $this->dispatch('captcha-reset');

            return;
        }

        RateLimiter::clear($this->throttleKey());

        $validated['password'] = Hash::make($validated['password']);
        unset($validated['captchaToken']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $segundos = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'name' => "Demasiados intentos de registro. Inténtalo de nuevo en {$segundos} segundos.",
        ]);
    }

    private function throttleKey(): string
    {
        return 'registro|'.request()->ip();
    }
}; ?>

<div
    x-data
    x-on:captcha-token.window="$wire.set('captchaToken', $event.detail)"
    x-on:captcha-reset.window="window.grecaptcha && window.grecaptcha.reset()"
>
    <form wire:submit="register">
        <!-- Campo señuelo: oculto para personas, algunos bots lo rellenan igualmente -->
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="sitio_web">Deja este campo vacío</label>
            <input wire:model="sitioWeb" id="sitio_web" type="text" tabindex="-1" autocomplete="off">
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        @if (config('services.recaptcha.site_key'))
            <div wire:ignore class="mt-4">
                <div
                    class="g-recaptcha"
                    data-sitekey="{{ config('services.recaptcha.site_key') }}"
                    data-callback="alisteCaptchaOkRegistro"
                    data-expired-callback="alisteCaptchaExpiradoRegistro"
                ></div>
            </div>
        @endif
        <x-input-error :messages="$errors->get('captchaToken')" class="mt-2" />

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 flex items-center">
        <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
        <span class="mx-3 text-xs text-gray-400 dark:text-gray-500 uppercase">{{ __('or') }}</span>
        <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
    </div>

    <a href="{{ route('auth.google.redirigir') }}"
        class="mt-6 flex items-center justify-center gap-3 w-full border border-gray-300 dark:border-gray-600 rounded-md py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
        <svg class="w-5 h-5" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z" />
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z" />
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z" />
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z" />
        </svg>
        {{ __('Continue with Google') }}
    </a>

    @if (config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script>
            function alisteCaptchaOkRegistro(token) {
                window.dispatchEvent(new CustomEvent('captcha-token', { detail: token }));
            }
            function alisteCaptchaExpiradoRegistro() {
                window.dispatchEvent(new CustomEvent('captcha-token', { detail: '' }));
            }
        </script>
    @endif
</div>
