<?php

namespace Tests\Unit;

use App\Models\RegistroLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;
use TypeError;

class RegistroLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_throwable_clasifica_una_exception_como_excepcion(): void
    {
        $registro = RegistroLog::registrarThrowable(new RuntimeException('Algo falló'));

        $this->assertSame(RegistroLog::TIPO_EXCEPCION, $registro->tipo);
        $this->assertSame('Algo falló', $registro->mensaje);
        $this->assertSame(RuntimeException::class, $registro->contexto['clase']);
    }

    public function test_registrar_throwable_clasifica_un_error_de_php_como_error(): void
    {
        $registro = RegistroLog::registrarThrowable(new TypeError('Tipo incorrecto'));

        $this->assertSame(RegistroLog::TIPO_ERROR, $registro->tipo);
    }

    public function test_registrar_tarea_programada_exitosa_es_informacion(): void
    {
        $registro = RegistroLog::registrarTareaProgramada('noticias:scrapear', exito: true, contexto: ['exit_code' => 0]);

        $this->assertSame(RegistroLog::TIPO_INFORMACION, $registro->tipo);
        $this->assertSame('noticias:scrapear', $registro->origen);
        $this->assertStringContainsString('finalizada correctamente', $registro->mensaje);
    }

    public function test_registrar_tarea_programada_fallida_es_error(): void
    {
        $registro = RegistroLog::registrarTareaProgramada('backup:base-datos', exito: false, contexto: ['exit_code' => 1]);

        $this->assertSame(RegistroLog::TIPO_ERROR, $registro->tipo);
        $this->assertStringContainsString('ha fallado', $registro->mensaje);
    }
}
