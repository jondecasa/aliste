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
     * Los administradores pueden editar cualquier evento. Los redactores solo
     * los suyos propios, y únicamente mientras no hayan pasado ya de fecha.
     *
     * Los eventos sin creador registrado (created_by null, creados antes de
     * que existiera este campo) se consideran "de nadie en particular": los
     * puede editar cualquier redactor del pueblo, igual que ocurría antes de
     * añadir esta restricción, para no dejarlos bloqueados para siempre.
     */
    public function puedeEditar(User $user): bool
    {
        if ($user->esAdministrador()) {
            return true;
        }

        $esSuyoOSinCreadorConocido = $this->created_by === null || $this->created_by === $user->id;

        return $esSuyoOSinCreadorConocido
            && $this->fecha_inicio->startOfDay()->gte(now()->startOfDay());
    }
}
