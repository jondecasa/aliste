<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'contenido_html',
    ];

    public static function obtener(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
