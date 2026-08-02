<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return BelongsToMany<Rol, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'role_user', 'user_id', 'role_id')
            ->withPivot('created_at');
    }

    /** Permisos concedidos directamente, al margen de los roles. */
    /** @return BelongsToMany<Permiso, $this> */
    public function permisosDirectos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permission_user', 'user_id', 'permission_id')
            ->withPivot(['otorgado_por', 'created_at']);
    }

    /** @return HasOne<Estudiante, $this> */
    public function estudiante(): HasOne
    {
        return $this->hasOne(Estudiante::class);
    }

    /** Solicitudes en las que este usuario figura como revisor. */
    /** @return HasMany<Solicitud, $this> */
    public function solicitudesRevisadas(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'revisor_id');
    }

    /** @return HasMany<Archivo, $this> */
    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class);
    }

    /** @return HasMany<Passkey, $this> */
    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class);
    }

    /** Indica si el usuario tiene el permiso, por rol o de forma directa. */
    public function tienePermiso(string $permiso): bool
    {
        return $this->permisosDirectos()->where('name', $permiso)->exists()
            || $this->roles()->whereHas('permisos', fn ($q) => $q->where('name', $permiso))->exists();
    }
}
