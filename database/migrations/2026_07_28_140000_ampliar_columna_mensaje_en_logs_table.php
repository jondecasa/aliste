<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // VARCHAR(255) se quedaba corto incluso para un Str::limit(...,1000)
        // (el sufijo "..." añade caracteres por encima del límite indicado).
        // TEXT evita perseguir números mágicos en cada punto que construye
        // el mensaje.
        DB::statement('ALTER TABLE logs MODIFY mensaje TEXT NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE logs MODIFY mensaje VARCHAR(255) NOT NULL');
    }
};
