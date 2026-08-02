<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'cedula', 'nombre', 'primer_apellido', 'segundo_apellido', 'activo'])]
class Estudiante extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombre} {$this->primer_apellido} {$this->segundo_apellido}");
    }

    /** Regla (c) de ES-01: el estudiante cursa al menos un plan terminal. */
    public function enPlanTerminal(): bool
    {
        return $this->planesEstudio()->where('clasificacion', 'Terminal')->exists();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<PlanEstudio, $this> */
    public function planesEstudio(): BelongsToMany
    {
        return $this->belongsToMany(PlanEstudio::class, 'estudiante_plan', 'estudiante_id', 'plan_estudio_id')
            ->withPivot(['nivel_actual', 'created_at']);
    }

    /** @return HasMany<HistorialAcademico, $this> */
    public function historialAcademico(): HasMany
    {
        return $this->hasMany(HistorialAcademico::class);
    }

    /** @return HasMany<Solicitud, $this> */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }
}
