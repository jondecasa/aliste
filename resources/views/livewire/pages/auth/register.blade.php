<?php

use App\Livewire\Concerns\VerificaCaptcha;
use App\Models\Pueblo;
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

new #[Layout('layouts.auth-split')] class extends Component
{
    use VerificaCaptcha;

    public string $name = '';
    public ?int $puebloId = null;
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $aceptaTerminos = false;
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
            'puebloId' => ['nullable', 'exists:pueblos,id'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'aceptaTerminos' => ['accepted'],
            'captchaToken' => [config('services.recaptcha.site_key') ? 'required' : 'nullable', 'string'],
        ], [
            'aceptaTerminos.accepted' => 'Debes aceptar los términos y la política de privacidad para continuar.',
            'captchaToken.required' => 'Por favor, confirma el captcha antes de continuar.',
        ]);

        if ($this->sitioWeb !== '' || ! $this->verificarCaptcha($validated['captchaToken'] ?? '')) {
            RateLimiter::hit($this->throttleKey(), 3600);

            $this->addError('captchaToken', 'No hemos podido verificar el captcha. Inténtalo de nuevo.');
            $this->dispatch('captcha-reset');

            return;
        }

        RateLimiter::clear($this->throttleKey());

        $user = User::create([
            'name' => $validated['name'],
            'pueblo_id' => $validated['puebloId'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function with(): array
    {
        return [
            'pueblos' => Pueblo::orderBy('nombre')->get(),
        ];
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
    <h1 class="mb-5 font-serif text-[28px] text-tinta sm:text-3xl">Únete a la comarca</h1>

    <form wire:submit="register" class="space-y-3">
        <!-- Campo señuelo: oculto para personas, algunos bots lo rellenan igualmente -->
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="sitio_web">Deja este campo vacío</label>
            <input wire:model="sitioWeb" id="sitio_web" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="flex gap-3">
            <div class="flex-1">
                <label for="name" class="mb-1 block text-sm font-semibold text-tinta">Nombre completo</label>
                <input
                    wire:model="name"
                    id="name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    class="block h-11 w-full rounded-lg border-tinta-borde bg-white px-4 text-sm text-tinta placeholder:text-tinta-muted focus:border-terracota focus:ring-terracota"
                >
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="w-2/5 flex-shrink-0">
                <label for="puebloId" class="mb-1 block text-sm font-semibold text-tinta">Pueblo</label>
                <select
                    wire:model="puebloId"
                    id="puebloId"
                    class="block h-11 w-full rounded-lg border-tinta-borde bg-white px-3 text-sm text-tinta focus:border-terracota focus:ring-terracota"
                >
                    <option value="">Sin asociar</option>
                    @foreach ($pueblos as $pueblo)
                        <option value="{{ $pueblo->id }}">{{ $pueblo->nombre }}</option>
                    @endforeach
                </select>
                @error('puebloId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-tinta">Correo electrónico</label>
            <input
                wire:model="email"
                id="email"
                type="email"
                required
                autocomplete="username"
                class="block h-11 w-full rounded-lg border-tinta-borde bg-white px-4 text-sm text-tinta placeholder:text-tinta-muted focus:border-terracota focus:ring-terracota"
            >
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3">
            <div class="flex-1">
                <label for="password" class="mb-1 block text-sm font-semibold text-tinta">Contraseña</label>
                <input
                    wire:model="password"
                    id="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="block h-11 w-full rounded-lg border-tinta-borde bg-white px-4 text-sm text-tinta placeholder:text-tinta-muted focus:border-terracota focus:ring-terracota"
                >
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex-1">
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-tinta">Confirmar</label>
                <input
                    wire:model="password_confirmation"
                    id="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="block h-11 w-full rounded-lg border-tinta-borde bg-white px-4 text-sm text-tinta placeholder:text-tinta-muted focus:border-terracota focus:ring-terracota"
                >
                @error('password_confirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-start gap-2.5">
            <input wire:model="aceptaTerminos" id="aceptaTerminos" type="checkbox" class="mt-0.5 rounded border-tinta-borde text-terracota focus:ring-terracota">
            <label for="aceptaTerminos" class="text-sm leading-snug text-tinta-muted">
                Acepto la
                <a href="{{ route('privacidad') }}" wire:navigate target="_blank" class="font-semibold text-terracota">política de privacidad</a>
                de Aliste.es.
            </label>
        </div>
        @error('aceptaTerminos') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        @if (config('services.recaptcha.site_key'))
            <div wire:ignore>
                <div
                    class="g-recaptcha"
                    data-sitekey="{{ config('services.recaptcha.site_key') }}"
                    data-callback="alisteCaptchaOkRegistro"
                    data-expired-callback="alisteCaptchaExpiradoRegistro"
                ></div>
            </div>
        @endif
        @error('captchaToken') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <button type="submit" class="mt-1 w-full rounded-full bg-terracota py-3 text-sm font-bold text-white transition hover:bg-terracota-dark">
            Crear cuenta
        </button>
    </form>

    <div class="my-4 flex items-center gap-3">
        <div class="h-px flex-1 bg-tinta-borde"></div>
        <span class="text-xs text-tinta-muted">o</span>
        <div class="h-px flex-1 bg-tinta-borde"></div>
    </div>

    <a href="{{ route('auth.google.redirigir') }}"
        class="flex w-full items-center justify-center gap-2.5 rounded-full border border-tinta-borde bg-white py-2.5 text-sm font-semibold text-tinta transition hover:bg-crema">
        <svg class="h-[18px] w-[18px]" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z" />
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z" />
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z" />
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z" />
        </svg>
        Continuar con Google
    </a>

    <p class="mt-4 text-center text-sm text-tinta-muted">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-terracota">Inicia sesión</a>
    </p>

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
