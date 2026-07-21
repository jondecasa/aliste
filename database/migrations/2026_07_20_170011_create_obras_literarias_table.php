<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obras_literarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pueblo_id')->nullable()->constrained('pueblos')->nullOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('autor')->nullable();
            $table->enum('tipo_obra', ['poesia', 'relato', 'novela', 'ensayo'])->nullable();
            $table->string('archivo')->nullable();
            $table->unsignedSmallInteger('anio')->nullable();
            $table->unsignedInteger('paginas')->nullable();
            $table->string('portada')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obras_literarias');
    }
};
