<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pueblo_id')->nullable()->constrained('pueblos')->nullOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('artista')->nullable();
            $table->string('album')->nullable();
            $table->string('archivo_audio')->nullable();
            $table->unsignedInteger('duracion')->nullable();
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('portada')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canciones');
    }
};
