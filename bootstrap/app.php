<?php

use App\Models\RegistroLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            try {
                RegistroLog::registrarThrowable($e);
            } catch (Throwable $errorAlRegistrar) {
                // Si falla el propio registro (p. ej. la BD no responde), que
                // no impida gestionar la excepción original: cae al log de
                // ficheros normal en vez de relanzar.
                Log::error('No se pudo guardar el registro en la tabla logs: '.$errorAlRegistrar->getMessage());
            }
        });
    })->create();
