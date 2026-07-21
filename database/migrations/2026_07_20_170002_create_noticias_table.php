<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pueblo_id')->nullable()->constrained('pueblos')->nullOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('extracto')->nullable();
            $table->longText('cuerpo')->nullable();
            $table->string('fuente_nombre')->nullable();
            $table->string('fuente_url')->nullable();
            $table->string('url_externa')->nullable();
            $table->string('imagen_portada')->nullable();
            $table->dateTime('publicado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
