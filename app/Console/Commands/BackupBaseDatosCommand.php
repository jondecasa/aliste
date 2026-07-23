<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupBaseDatosCommand extends Command
{
    protected $signature = 'backup:base-datos';

    protected $description = 'Genera un volcado comprimido de la base de datos y elimina los backups más antiguos';

    private const MAXIMO_BACKUPS = 10;

    public function handle(): int
    {
        $directorio = storage_path('app/backups');

        File::ensureDirectoryExists($directorio);

        $conexion = config('database.default');
        $config = config("database.connections.{$conexion}");

        $archivo = $directorio.'/backup_'.now()->format('Y-m-d_His').'.sql.gz';

        $comando = sprintf(
            'mysqldump --single-transaction --quick -h %s -P %s -u %s %s | gzip > %s',
            escapeshellarg($config['host']),
            escapeshellarg((string) $config['port']),
            escapeshellarg($config['username']),
            escapeshellarg($config['database']),
            escapeshellarg($archivo)
        );

        $resultado = Process::env(['MYSQL_PWD' => $config['password']])
            ->timeout(300)
            ->run($comando);

        if (! $resultado->successful()) {
            $this->error('Fallo al generar el backup: '.$resultado->errorOutput());

            if (File::exists($archivo)) {
                File::delete($archivo);
            }

            return self::FAILURE;
        }

        $this->info("Backup generado: {$archivo}");

        $this->rotar($directorio);

        return self::SUCCESS;
    }

    private function rotar(string $directorio): void
    {
        collect(File::files($directorio))
            ->sortByDesc(fn ($archivo) => $archivo->getMTime())
            ->skip(self::MAXIMO_BACKUPS)
            ->each(function ($archivo) {
                File::delete($archivo->getPathname());
                $this->info("Backup antiguo eliminado: {$archivo->getFilename()}");
            });
    }
}
