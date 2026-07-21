<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pueblo extends Model
{
    protected $table = 'pueblos';

    protected $fillable = [
        'nombre',
        'slug',
        'latitud',
        'longitud',
        'descripcion',
        'portada',
        'poblacion',
        'altitud',
        'es_cabecera',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'es_cabecera' => 'boolean',
        ];
    }

    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class);
    }

    public function puntosInteres(): HasMany
    {
        return $this->hasMany(PuntoInteres::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    public function canciones(): HasMany
    {
        return $this->hasMany(Cancion::class);
    }

    public function obrasLiterarias(): HasMany
    {
        return $this->hasMany(ObraLiteraria::class);
    }
}
