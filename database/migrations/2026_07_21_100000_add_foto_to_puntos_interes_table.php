<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('puntos_interes', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('direccion');
        });
    }

    public function down(): void
    {
        Schema::table('puntos_interes', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
