<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Evento extends Model
{
    /**
     * Los eventos que empiezan antes de esta hora se consideran parte de la
     * "noche" del día anterior (p. ej. un grupo tocando a las 00:30 cuenta
     * como el último evento del día previo, no como el primero del nuevo).
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
     * Día al que pertenece el evento a efectos de agrupación en el
     * calendario: si empieza de madrugada (antes de HORA_CORTE_DIA), se
     * agrupa con el día anterior.
     */
    public function getDiaLogicoAttribute(): string
    {
        $fecha = $this->fecha_inicio;

        return $fecha->hour < self::HORA_CORTE_DIA
            ? $fecha->copy()->subDay()->toDateString()
            : $fecha->toDateString();
    }

    /**
     * Igual que fecha_inicio, pero con la fecha desplazada a dia_logico y
     * conservando la hora real. Sirve para que el calendario coloque el
     * evento en la celda del día correcto sin dejar de mostrar su hora real.
     */
    public function getInicioCalendarioAttribute(): Carbon
    {
        return Carbon::parse($this->dia_logico.' '.$this->fecha_inicio->format('H:i:s'), $this->fecha_inicio->getTimezone());
    }

    /**
     * Minutos desde la medianoche de dia_logico, para poder ordenar dentro
     * de un mismo día: un evento a las 00:30 debe figurar el último, no el
     * primero, así que se le suman 24h "virtuales".
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
