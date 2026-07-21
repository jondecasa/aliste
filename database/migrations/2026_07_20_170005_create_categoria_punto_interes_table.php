<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_punto_interes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('punto_interes_id')->constrained('puntos_interes')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['punto_interes_id', 'categoria_id'], 'categoria_punto_interes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_punto_interes');
    }
};
