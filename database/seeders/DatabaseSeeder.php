<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database con el contenido actual del sitio.
     * No crea usuarios: esos se gestionan aparte (registro/Google, o el
     * primer administrador se crea a mano en producción).
     */
    public function run(): void
    {
        $this->call([
            PuebloSeeder::class,
            CategoriaSeeder::class,
            PuntoInteresSeeder::class,
            ServicioSeeder::class,
            BannerSeeder::class,
            NoticiaSeeder::class,
        ]);
    }
}
