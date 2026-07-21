<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE categorias MODIFY COLUMN grupo ENUM('noticia', 'punto_interes', 'servicio', 'cancion', 'obra_literaria', 'evento') NOT NULL");

        Schema::table('categorias', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('grupo');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        DB::statement("ALTER TABLE categorias MODIFY COLUMN grupo ENUM('noticia', 'punto_interes', 'servicio', 'cancion', 'obra_literaria') NOT NULL");
    }
};
