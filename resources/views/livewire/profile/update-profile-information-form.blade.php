<?php

use App\Models\Pueblo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public ?int $puebloId = null;
    public $avatar = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->puebloId = Auth::user()->pueblo_id;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'puebloId' => ['nullable', 'exists:pueblos,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $datos = [
            'name' => $validated['name'],
            'pueblo_id' => $user->esRedactor() ? $user->pueblo_id : $validated['puebloId'],
        ];

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $datos['avatar'] = $this->avatar->store('avatars', 'public');
        }

        $user->fill($datos);
        $user->save();

        $this->avatar = null;

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="avatar" value="Foto de perfil" />

            <div class="mt-2 flex items-center gap-4">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="w-16 h-16 rounded-full object-cover">
                @elseif (auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-gray-200"></div>
                @endif

                <input wire:model="avatar" id="avatar" type="file" accept="image/*" class="block text-sm" />
            </div>
            <div wire:loading wire:target="avatar" class="text-xs text-gray-500 mt-1">Subiendo imagen...</div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input :value="auth()->user()->email" id="email" type="email" class="mt-1 block w-full bg-gray-100" disabled />
            <p class="mt-1 text-sm text-gray-500">El email de acceso no se puede modificar.</p>

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="puebloId" value="Tu pueblo" />

            @if (auth()->user()->esRedactor())
                <x-text-input :value="auth()->user()->pueblo?->nombre ?? 'Sin asociar'" id="puebloId" type="text" class="mt-1 block w-full bg-gray-100" disabled />
                <p class="mt-1 text-sm text-gray-500">Como redactor, tu pueblo lo asigna un administrador y no se puede cambiar aquí.</p>
            @else
                <select wire:model="puebloId" id="puebloId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Sin asociar</option>
                    @foreach (Pueblo::orderBy('nombre')->get() as $pueblo)
                        <option value="{{ $pueblo->id }}">{{ $pueblo->nombre }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500">Asóciate a tu pueblo para aparecer más adelante en su apartado de "gente".</p>
            @endif

            <x-input-error class="mt-2" :messages="$errors->get('puebloId')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
