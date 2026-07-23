<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $notifOtrosPueblos = true;
    public bool $notifMiPueblo = true;

    public function mount(): void
    {
        $this->notifOtrosPueblos = (bool) Auth::user()->notif_eventos_otros_pueblos;
        $this->notifMiPueblo = (bool) Auth::user()->notif_eventos_mi_pueblo;
    }

    public function updatedNotifOtrosPueblos(bool $valor): void
    {
        Auth::user()->update(['notif_eventos_otros_pueblos' => $valor]);
    }

    public function updatedNotifMiPueblo(bool $valor): void
    {
        Auth::user()->update(['notif_eventos_mi_pueblo' => $valor]);
    }
}; ?>

<div
    class="max-w-xl"
    x-data="{
        suscrito: false,
        cargando: true,
        error: null,
        async comprobar() {
            this.suscrito = await window.PushNotificaciones.estadoSuscripcion();
            this.cargando = false;

            if (! this.suscrito && window.Notification && Notification.permission === 'default') {
                this.activar();
            }
        },
        async activar() {
            this.error = null;
            try {
                await window.PushNotificaciones.suscribirNotificaciones('{{ config('webpush.vapid.public_key') }}');
                this.suscrito = true;
            } catch (e) {
                this.error = e.message;
            }
        },
        async desactivar() {
            this.error = null;
            try {
                await window.PushNotificaciones.desuscribirNotificaciones();
                this.suscrito = false;
            } catch (e) {
                this.error = e.message;
            }
        },
    }"
    x-init="comprobar()"
>
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Notificaciones</h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Recibe avisos con los eventos de la comarca para cada día.
    </p>

    <div class="mt-4" x-cloak x-show="!cargando">
        <button
            type="button"
            x-show="!suscrito"
            x-on:click="activar()"
            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
            Activar notificaciones
        </button>

        <button
            type="button"
            x-show="suscrito"
            x-on:click="desactivar()"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            Desactivar notificaciones
        </button>

        <p x-show="suscrito" class="mt-2 text-sm text-green-600 dark:text-green-400">Notificaciones activadas.</p>
        <p x-show="error" x-text="error" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>

        <div class="mt-4 space-y-2">
            <label class="flex items-start gap-2">
                <input type="checkbox" wire:model.live="notifOtrosPueblos" class="mt-0.5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="text-sm text-gray-600 dark:text-gray-400">Eventos destacados de otros pueblos de la comarca</span>
            </label>

            @if (auth()->user()->pueblo_id)
                <label class="flex items-start gap-2">
                    <input type="checkbox" wire:model.live="notifMiPueblo" class="mt-0.5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Eventos de mi pueblo ({{ auth()->user()->pueblo->nombre }})</span>
                </label>
            @endif
        </div>
    </div>
</div>
