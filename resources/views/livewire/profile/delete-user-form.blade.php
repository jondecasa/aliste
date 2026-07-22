<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function mount(): void
    {
        $this->password = '';
    }

    public function requierePassword(): bool
    {
        return Auth::user()->google_id === null;
    }

    public function deleteUser(Logout $logout): void
    {
        if ($this->requierePassword()) {
            try {
                $this->validate([
                    'password' => ['required', 'string', 'current_password'],
                ]);
            } catch (ValidationException $e) {
                $this->reset('password');

                throw $e;
            }
        }

        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->pushSubscriptions()->delete();

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Eliminar cuenta
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Una vez elimines tu cuenta, todos tus datos se borrarán de forma permanente. Descarga cualquier
            información que quieras conservar antes de continuar.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirmar-eliminar-cuenta')"
    >
        Eliminar cuenta
    </x-danger-button>

    <x-modal name="confirmar-eliminar-cuenta" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                ¿Seguro que quieres eliminar tu cuenta?
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                @if ($this->requierePassword())
                    Esta acción no se puede deshacer. Introduce tu contraseña para confirmar que quieres eliminar
                    tu cuenta de forma permanente.
                @else
                    Esta acción no se puede deshacer y eliminará permanentemente tu cuenta.
                @endif
            </p>

            @if ($this->requierePassword())
                <div class="mt-6">
                    <x-input-label for="password" value="Contraseña" class="sr-only" />

                    <x-text-input
                        wire:model="password"
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Contraseña"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
            @endif

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
