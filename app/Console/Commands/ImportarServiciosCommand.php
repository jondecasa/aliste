<?php

namespace App\Console\Commands;

use Database\Seeders\ServicioSeeder;
use Illuminate\Console\Command;

class ImportarServiciosCommand extends Command
{
    protected $signature = 'servicios:importar';

    protected $description = 'Importa el listado de servicios publicado en aliste.info';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => ServicioSeeder::class]);

        return self::SUCCESS;
    }
}
