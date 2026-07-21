<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Noticia extends Model
{
    protected $table = 'noticias';

    protected $fillable = [
        'pueblo_id',
        'titulo',
        'slug',
        'extracto',
        'cuerpo',
        'fuente_nombre',
        'fuente_url',
        'url_externa',
        'imagen_portada',
        'publicado_en',
    ];

    protected function casts(): array
    {
        return [
            'publicado_en' => 'datetime',
        ];
    }

    public function pueblo(): BelongsTo
    {
        return $this->belongsTo(Pueblo::class);
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_noticia');
    }
}
