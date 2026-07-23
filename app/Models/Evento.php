<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Evento extends Model
{
    /**
     * Los eventos que empiezan antes de esta hora se consideran la
     * continuación nocturna de su propio día (p. ej. un grupo tocando a la
     * 1:00 cuenta como el último evento de ese día, no el primero, aunque
     * cronológicamente sea la hora más temprana).
     */
    private const HORA_CORTE_DIA = 5;

    protected $table = 'eventos';

    protected $fillable = [
        'pueblo_id',
        'categoria_id',
        'created_by',
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

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? Storage::disk('public')->url($this->imagen) : null;
    }

    /**
     * Minutos desde la medianoche de su propio día, pensados para ordenar
     * los eventos dentro de ese mismo día en el calendario: un evento a la
     * 1:00 debe figurar el último (no el primero), así que se le suman 24h
     * "virtuales" en vez de tratarlo como si fuera muy temprano.
     */
    public function getOrdenLogicoAttribute(): int
    {
        $minutos = $this->fecha_inicio->hour * 60 + $this->fecha_inicio->minute;

        return $this->fecha_inicio->hour < self::HORA_CORTE_DIA
            ? $minutos + (24 * 60)
            : $minutos;
    }

    /**
     * Los administradores pueden editar cualquier evento. Los redactores
     * pueden editar cualquier evento de su pueblo (el ámbito de pueblo ya se
     * comprueba aparte) siempre que su fecha sea hoy o futura; no pasado.
     */
    public function puedeEditar(User $user): bool
    {
        if ($user->esAdministrador()) {
            return true;
        }

        return $this->fecha_inicio->startOfDay()->gte(now()->startOfDay());
    }
}
