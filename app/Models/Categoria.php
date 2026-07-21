<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'slug',
        'grupo',
        'color',
    ];

    public function noticias(): BelongsToMany
    {
        return $this->belongsToMany(Noticia::class, 'categoria_noticia');
    }

    public function puntosInteres(): BelongsToMany
    {
        return $this->belongsToMany(PuntoInteres::class, 'categoria_punto_interes');
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'categoria_servicio');
    }

    public function canciones(): BelongsToMany
    {
        return $this->belongsToMany(Cancion::class, 'categoria_cancion');
    }

    public function obrasLiterarias(): BelongsToMany
    {
        return $this->belongsToMany(ObraLiteraria::class, 'categoria_obra');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    public function scopeDeGrupo(Builder $query, string $grupo): Builder
    {
        return $query->where('grupo', $grupo);
    }
}
