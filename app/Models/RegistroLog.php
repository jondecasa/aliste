<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

class RegistroLog extends Model
{
    protected $table = 'logs';

    public const TIPO_ERROR = 'error';
    public const TIPO_EXCEPCION = 'excepcion';
    public const TIPO_INFORMACION = 'informacion';

    public const TIPOS = [
        self::TIPO_ERROR => 'Error',
        self::TIPO_EXCEPCION => 'Excepción',
        self::TIPO_INFORMACION => 'Información',
    ];

    protected $fillable = [
        'tipo',
        'origen',
        'mensaje',
        'contexto',
    ];

    protected function casts(): array
    {
        return [
            'contexto' => 'array',
        ];
    }

    /**
     * Registra un error o excepción no controlada. Se clasifica como "error"
     * si es un \Error de PHP (fallo del propio código: TypeError, etc.) o
     * como "excepcion" si es una \Exception (el resto de casos).
     */
    public static function registrarThrowable(Throwable $e, ?string $origen = null): self
    {
        return static::create([
            'tipo' => $e instanceof \Error ? self::TIPO_ERROR : self::TIPO_EXCEPCION,
            'origen' => $origen ?? get_class($e),
            'mensaje' => Str::limit($e->getMessage() ?: get_class($e), 1000),
            'contexto' => [
                'clase' => get_class($e),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'traza' => Str::limit($e->getTraceAsString(), 5000),
                'url' => app()->runningInConsole() ? null : request()?->fullUrl(),
                'metodo_http' => app()->runningInConsole() ? null : request()?->method(),
            ],
        ]);
    }

    /**
     * Registra el lanzamiento de una tarea programada, haya ido bien o mal.
     */
    public static function registrarTareaProgramada(string $comando, bool $exito, array $contexto = []): self
    {
        return static::create([
            'tipo' => $exito ? self::TIPO_INFORMACION : self::TIPO_ERROR,
            'origen' => $comando,
            'mensaje' => $exito
                ? "Tarea programada \"{$comando}\" finalizada correctamente."
                : "Tarea programada \"{$comando}\" ha fallado.",
            'contexto' => $contexto ?: null,
        ]);
    }
}
