<?php

namespace App\Providers;

use App\Models\RegistroLog;
use App\Models\User;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('administrar', fn (User $user) => $user->esAdministrador());

        Gate::define(
            'redactar-noticias',
            fn (User $user) => $user->esAdministrador() || $user->esRedactor()
        );

        Gate::define(
            'gestionar-contenido-pueblo',
            fn (User $user) => $user->esAdministrador() || ($user->esRedactor() && $user->pueblo_id !== null)
        );

        Event::listen(function (ScheduledTaskFinished $event) {
            RegistroLog::registrarTareaProgramada(
                comando: $event->task->description ?: $event->task->command,
                exito: (int) $event->task->exitCode === 0,
                contexto: [
                    'exit_code' => $event->task->exitCode,
                    'duracion_segundos' => round($event->runtime, 2),
                ],
            );
        });
    }
}
