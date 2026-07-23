<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Cancion extends Model
{
    protected $table = 'canciones';

    protected $fillable = [
        'pueblo_id',
        'titulo',
        'slug',
        'artista',
        'album',
        'duracion',
        'anio',
        'portada',
        'descripcion',
        'letra',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function pueblo(): BelongsTo
    {
        return $this->belongsTo(Pueblo::class);
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_cancion');
    }

    public function audios(): HasMany
    {
        return $this->hasMany(AudioCancion::class)->orderBy('orden');
    }

    public function getPortadaUrlAttribute(): ?string
    {
        return $this->portada ? Storage::disk('public')->url($this->portada) : null;
    }
}
