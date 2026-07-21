<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ObraLiteraria extends Model
{
    protected $table = 'obras_literarias';

    protected $fillable = [
        'pueblo_id',
        'titulo',
        'slug',
        'autor',
        'tipo_obra',
        'archivo',
        'anio',
        'paginas',
        'portada',
        'descripcion',
    ];

    public function pueblo(): BelongsTo
    {
        return $this->belongsTo(Pueblo::class);
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_obra');
    }
}
