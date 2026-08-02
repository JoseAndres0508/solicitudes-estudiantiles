<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'carrera_id', 'codigo', 'nombre', 'es_servicio', 'es_cuello_botella',
    'requiere_laboratorio', 'tipo_laboratorio', 'activo',
])]
class Curso extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'es_servicio' => 'boolean',
            'es_cuello_botella' => 'boolean',
            'requiere_laboratorio' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /** @return BelongsTo<Carrera, $this> */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    /** Niveles de plan en que aparece, con los créditos que vale en cada uno. */
    /** @return BelongsToMany<Nivel, $this> */
    public function niveles(): BelongsToMany
    {
        return $this->belongsToMany(Nivel::class, 'curso_nivel', 'curso_id', 'nivel_id')
            ->withPivot(['creditos', 'created_at']);
    }

    /** Requisitos que este curso exige para poder matricularse. */
    /** @return HasMany<Requisito, $this> */
    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class, 'curso_exige_id');
    }

    /** Cursos que exigen a este como requisito. */
    /** @return HasMany<Requisito, $this> */
    public function esRequisitoDe(): HasMany
    {
        return $this->hasMany(Requisito::class, 'curso_requerido_id');
    }

    /** @return HasMany<ReglaLevantamiento, $this> */
    public function reglasLevantamiento(): HasMany
    {
        return $this->hasMany(ReglaLevantamiento::class)->orderBy('orden');
    }

    /** @return HasMany<HistorialAcademico, $this> */
    public function historialAcademico(): HasMany
    {
        return $this->hasMany(HistorialAcademico::class);
    }

    /** @return HasMany<ConvalidacionHistorica, $this> */
    public function convalidacionesHistoricas(): HasMany
    {
        return $this->hasMany(ConvalidacionHistorica::class);
    }
}
