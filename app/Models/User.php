<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions;

    public const ROL_ADMINISTRADOR = 'administrador';
    public const ROL_REDACTOR = 'redactor';
    public const ROL_INVITADO = 'invitado';

    /**
     * Valores por defecto en memoria, iguales a los definidos a nivel de BD.
     * Evita nulls en una instancia recién creada antes de recargarla de la BD.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'tema' => 'claro',
        'notif_eventos_otros_pueblos' => true,
        'notif_eventos_mi_pueblo' => true,
        'bloqueado' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'rol',
        'bloqueado',
        'pueblo_id',
        'avatar',
        'tema',
        'notif_eventos_otros_pueblos',
        'notif_eventos_mi_pueblo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notif_eventos_otros_pueblos' => 'boolean',
            'notif_eventos_mi_pueblo' => 'boolean',
            'bloqueado' => 'boolean',
        ];
    }

    public function pueblo(): BelongsTo
    {
        return $this->belongsTo(Pueblo::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : null;
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    public function esRedactor(): bool
    {
        return $this->rol === self::ROL_REDACTOR;
    }

    public function esInvitado(): bool
    {
        return $this->rol === self::ROL_INVITADO;
    }

    public function estaBloqueado(): bool
    {
        return (bool) $this->bloqueado;
    }

    /**
     * Un usuario bloqueado no debe poder completar el flujo de "olvidé mi
     * contraseña": se omite el envío del email en vez de dejar que el
     * enlace de recuperación llegue a una cuenta que no puede acceder.
     */
    public function sendPasswordResetNotification($token): void
    {
        if ($this->estaBloqueado()) {
            return;
        }

        parent::sendPasswordResetNotification($token);
    }

    public function prefiereTemaOscuro(): bool
    {
        return $this->tema === 'oscuro';
    }
}
