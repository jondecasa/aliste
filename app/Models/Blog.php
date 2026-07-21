<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $table = 'blogs';

    protected $fillable = [
        'nombre',
        'slug',
        'url',
        'es_externo',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'es_externo' => 'boolean',
        ];
    }
}
