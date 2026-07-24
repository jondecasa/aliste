<?php

namespace Tests\Feature;

use App\Models\RegistroLog;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RegistroExcepcionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_reportar_una_excepcion_no_controlada_crea_un_registro(): void
    {
        app(ExceptionHandler::class)->report(new RuntimeException('Fallo de prueba en el manejador global'));

        $this->assertDatabaseHas('logs', [
            'tipo' => RegistroLog::TIPO_EXCEPCION,
            'mensaje' => 'Fallo de prueba en el manejador global',
        ]);
    }

    public function test_una_tarea_programada_exitosa_registra_una_entrada_de_informacion(): void
    {
        $task = new Event(app(CacheEventMutex::class), 'php artisan prueba:comando');
        $task->description = 'Tarea de prueba';
        $task->exitCode = 0;

        event(new ScheduledTaskFinished($task, 1.5));

        $this->assertDatabaseHas('logs', [
            'tipo' => RegistroLog::TIPO_INFORMACION,
            'origen' => 'Tarea de prueba',
        ]);
    }

    public function test_una_tarea_programada_fallida_registra_una_entrada_de_error(): void
    {
        $task = new Event(app(CacheEventMutex::class), 'php artisan prueba:fallo');
        $task->description = 'Tarea de prueba fallida';
        $task->exitCode = 1;

        event(new ScheduledTaskFinished($task, 0.2));

        $this->assertDatabaseHas('logs', [
            'tipo' => RegistroLog::TIPO_ERROR,
            'origen' => 'Tarea de prueba fallida',
        ]);
    }
}
