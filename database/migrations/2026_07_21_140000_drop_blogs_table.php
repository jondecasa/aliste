<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('blogs');
    }

    public function down(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('url')->nullable();
            $table->boolean('es_externo')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }
};
