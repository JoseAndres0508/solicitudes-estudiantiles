<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('planes_estudio')]
#[Fillable(['carrera_id', 'nombre', 'anio_implementacion', 'clasificacion', 'fecha_cierre_matricula'])]
class PlanEstudio extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['fecha_cierre_matricula' => 'date'];
    }

    /** Base de la regla (c) de ES-01: pertenencia a plan terminal. */
    public function esTerminal(): bool
    {
        return $this->clasificacion === 'Terminal';
    }

    /** @return BelongsTo<Carrera, $this> */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    /** @return HasMany<Nivel, $this> */
    public function niveles(): HasMany
    {
        return $this->hasMany(Nivel::class)->orderBy('numero');
    }

    /** @return HasMany<Requisito, $this> */
    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class);
    }

    /** @return BelongsToMany<Estudiante, $this> */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_plan', 'plan_estudio_id', 'estudiante_id')
            ->withPivot(['nivel_actual', 'created_at']);
    }
}
