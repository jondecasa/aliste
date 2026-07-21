<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cancion extends Model
{
    protected $table = 'canciones';

    protected $fillable = [
        'pueblo_id',
        'titulo',
        'slug',
        'artista',
        'album',
        'archivo_audio',
        'duracion',
        'anio',
        'portada',
        'descripcion',
    ];

    public function pueblo(): BelongsTo
    {
        return $this->belongsTo(Pueblo::class);
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_cancion');
    }
}
