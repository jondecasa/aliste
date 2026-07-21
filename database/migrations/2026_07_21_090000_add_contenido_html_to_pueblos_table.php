<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pueblos', function (Blueprint $table) {
            $table->longText('contenido_html')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('pueblos', function (Blueprint $table) {
            $table->dropColumn('contenido_html');
        });
    }
};
