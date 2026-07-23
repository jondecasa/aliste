<?php

use App\Livewire\Concerns\VerificaCaptcha;
use App\Mail\ContactoEnviado;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    use VerificaCaptcha;

    public string $nombre = '';
    public string $email = '';
    public string $asunto = '';
    public string $descripcion = '';
    public string $captchaToken = '';
    public bool $enviado = false;

    public function mount(): void
    {
        View::share('title', 'Contacto · Aliste.es');
        View::share('ogDescripcion', 'Ponte en contacto con Aliste.es.');
    }

    public function enviar(): void
    {
        $this->ensureIsNotRateLimited();

        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'asunto' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'captchaToken' => [config('services.recaptcha.site_key') ? 'required' : 'nullable', 'string'],
        ], [
            'captchaToken.required' => 'Por favor, confirma el captcha antes de enviar.',
        ]);

        if (! $this->verificarCaptcha($datos['captchaToken'])) {
            RateLimiter::hit($this->throttleKey(), 3600);

            $this->addError('captchaToken', 'No hemos podido verificar el captcha. Inténtalo de nuevo.');
            $this->dispatch('captcha-reset');

            return;
        }

        RateLimiter::hit($this->throttleKey(), 3600);

        Mail::to(config('mail.contact_to'))->send(new ContactoEnviado(
            nombreRemitente: $datos['nombre'],
            emailRemitente: $datos['email'],
            asunto: $datos['asunto'],
            descripcion: $datos['descripcion'],
        ));

        $this->reset(['nombre', 'email', 'asunto', 'descripcion', 'captchaToken']);
        $this->dispatch('captcha-reset');
        $this->enviado = true;
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 2)) {
            return;
        }

        event(new Lockout(request()));

        $segundos = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'descripcion' => "Demasiados mensajes enviados. Inténtalo de nuevo en {$segundos} segundos.",
        ]);
    }

    private function throttleKey(): string
    {
        return 'contacto|'.request()->ip();
    }
}; ?>

<div>
    <div class="relative h-[160px] sm:h-[220px] mx-4 sm:mx-8 mt-4 sm:mt-8 rounded-2xl overflow-hidden bg-tinta flex items-end">
        <div class="relative w-full p-6 sm:p-10">
            <h1 class="font-serif text-2xl sm:text-4xl text-white">Contacta con nosotros</h1>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-8 py-10 sm:py-14">
        @if ($enviado)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-[0_8px_24px_rgba(60,30,10,0.08)] text-center">
                <div class="font-serif text-xl text-tinta mb-2">¡Mensaje enviado!</div>
                <p class="text-tinta-muted text-sm">Gracias por escribirnos, te responderemos lo antes posible.</p>
                <button wire:click="$set('enviado', false)" class="mt-6 text-sm text-terracota font-semibold">
                    Enviar otro mensaje
                </button>
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 shadow-[0_8px_24px_rgba(60,30,10,0.08)]"
                x-data
                x-on:captcha-token.window="$wire.set('captchaToken', $event.detail)"
                x-on:captcha-reset.window="window.grecaptcha && window.grecaptcha.reset()"
            >
                <form wire:submit="enviar" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-tinta">Nombre</label>
                            <input wire:model="nombre" id="nombre" type="text" class="mt-1 block w-full rounded-md border-tinta-borde focus:border-terracota focus:ring-terracota text-sm">
                            @error('nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-tinta">Email</label>
                            <input wire:model="email" id="email" type="email" class="mt-1 block w-full rounded-md border-tinta-borde focus:border-terracota focus:ring-terracota text-sm">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="asunto" class="block text-sm font-medium text-tinta">Asunto</label>
                        <input wire:model="asunto" id="asunto" type="text" class="mt-1 block w-full rounded-md border-tinta-borde focus:border-terracota focus:ring-terracota text-sm">
                        @error('asunto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-tinta">Descripción</label>
                        <textarea wire:model="descripcion" id="descripcion" rows="6" class="mt-1 block w-full rounded-md border-tinta-borde focus:border-terracota focus:ring-terracota text-sm"></textarea>
                        @error('descripcion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if (config('services.recaptcha.site_key'))
                        <div wire:ignore>
                            <div
                                class="g-recaptcha"
                                data-sitekey="{{ config('services.recaptcha.site_key') }}"
                                data-callback="alisteCaptchaOk"
                                data-expired-callback="alisteCaptchaExpirado"
                            ></div>
                        </div>
                    @else
                        <p class="text-xs text-amber-600 bg-amber-50 rounded-md p-3">
                            El captcha no está configurado todavía (falta RECAPTCHA_SITE_KEY en el servidor).
                        </p>
                    @endif
                    @error('captchaToken') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    <button type="submit" class="bg-terracota text-white px-7 py-3 rounded-full font-bold text-sm">
                        Enviar mensaje
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if (config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script>
            function alisteCaptchaOk(token) {
                window.dispatchEvent(new CustomEvent('captcha-token', { detail: token }));
            }
            function alisteCaptchaExpirado() {
                window.dispatchEvent(new CustomEvent('captcha-token', { detail: '' }));
            }
        </script>
    @endif
</div>
