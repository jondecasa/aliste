<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'pueblo_id',
        'nombre',
        'slug',
        'prioridad',
        'visto_en_importacion_en',
        'revisado_en',
        'direccion',
        'codigo_postal',
        'telefono_1',
        'telefono_2',
        'sitio_web',
        'latitud',
        'longitud',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'visto_en_importacion_en' => 'datetime',
            'revisado_en' => 'datetime',
        ];
    }

    public function pueblo(): BelongsTo
    {
        return $this->belongsTo(Pueblo::class);
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_servicio');
    }

    /**
     * Enlaces a Google Maps para comprobar rápidamente si el negocio sigue
     * existiendo en la realidad (aliste.info, la fuente de la importación
     * automática, lleva años sin actualizarse y no sirve como referencia de
     * qué sigue abierto). Se ofrecen dos vistas porque las coordenadas del
     * scraping original no siempre son fiables: una por coordenadas (si
     * existen) y otra por nombre + pueblo, para poder contrastarlas.
     */
    public function getEnlaceMapsCoordenadasAttribute(): ?string
    {
        if ($this->latitud === null || $this->longitud === null) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.$this->latitud.','.$this->longitud;
    }

    public function getEnlaceMapsNombreAttribute(): string
    {
        $consulta = trim($this->nombre.', '.($this->pueblo?->nombre ?? '').', Zamora');

        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($consulta);
    }

    /**
     * Servicios que en algún momento vinieron de una importación de
     * aliste.info pero no aparecieron en la última pasada — candidatos a
     * haber cerrado o desaparecido de la fuente original. Excluye los
     * creados a mano en el panel (esos nunca tienen visto_en_importacion_en).
     */
    public function scopeNoVistosEnUltimaImportacion($query)
    {
        $ultimaImportacion = static::max('visto_en_importacion_en');

        if (! $ultimaImportacion) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereNotNull('visto_en_importacion_en')
            ->where('visto_en_importacion_en', '<', $ultimaImportacion);
    }
}
