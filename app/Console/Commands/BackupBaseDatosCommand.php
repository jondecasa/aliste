<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;

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

        $gz = gzopen($archivo, 'wb9');

        if ($gz === false) {
            $this->error("No se pudo crear el archivo de backup: {$archivo}");

            return self::FAILURE;
        }

        // Se pasa el comando como array (no como string) para que Symfony
        // Process escape los argumentos internamente sin necesitar
        // escapeshellarg(), que algunos hostings (p. ej. Plesk) deshabilitan
        // por seguridad. La compresión se hace aquí con zlib en vez de
        // canalizar la salida a un "gzip" externo por un pipe de shell.
        $resultado = Process::env(['MYSQL_PWD' => $config['password']])
            ->timeout(300)
            ->run([
                'mysqldump',
                '--single-transaction',
                '--quick',
                '-h', (string) $config['host'],
                '-P', (string) $config['port'],
                '-u', $config['username'],
                $config['database'],
            ], function (string $tipo, string $buffer) use ($gz) {
                if ($tipo === SymfonyProcess::OUT) {
                    gzwrite($gz, $buffer);
                }
            });

        gzclose($gz);

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
