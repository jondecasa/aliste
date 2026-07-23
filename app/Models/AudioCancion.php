<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AudioCancion extends Model
{
    protected $table = 'audios_cancion';

    protected $fillable = [
        'cancion_id',
        'archivo',
        'titulo',
        'orden',
    ];

    public function cancion(): BelongsTo
    {
        return $this->belongsTo(Cancion::class);
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo ? Storage::disk('public')->url($this->archivo) : null;
    }
}
