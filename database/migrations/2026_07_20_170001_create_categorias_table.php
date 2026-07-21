<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug');
            $table->enum('grupo', [
                'noticia',
                'punto_interes',
                'servicio',
                'cancion',
                'obra_literaria',
            ]);
            $table->timestamps();

            $table->unique(['slug', 'grupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
