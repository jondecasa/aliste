<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('canciones', function (Blueprint $table) {
            $table->dropColumn('archivo_audio');
            $table->longText('letra')->nullable()->after('descripcion');
        });

        DB::statement('ALTER TABLE canciones MODIFY descripcion LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE canciones MODIFY descripcion TEXT NULL');

        Schema::table('canciones', function (Blueprint $table) {
            $table->dropColumn('letra');
            $table->string('archivo_audio')->nullable();
        });
    }
};
