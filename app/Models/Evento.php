<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'pueblo_id',
        'categoria_id',
        'titulo',
        'slug',
        'descripcion',
        'lugar',
        'imagen',
        'fecha_inicio',
        'fecha_fin',
        'es_principal',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'es_principal' => 'boolean',
        ];
    }

    public function pueblo(): BelongsTo
    {
        return $this->belongsTo(Pueblo::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? Storage::disk('public')->url($this->imagen) : null;
    }
}
