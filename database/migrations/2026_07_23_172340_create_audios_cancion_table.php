<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audios_cancion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancion_id')->constrained('canciones')->cascadeOnDelete();
            $table->string('archivo');
            $table->string('titulo')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audios_cancion');
    }
};
