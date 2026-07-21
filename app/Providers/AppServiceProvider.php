<?php

namespace App\Providers;

use App\Models\User;
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
    }
}
