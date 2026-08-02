<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('reglas_levantamiento')]
#[Fillable([
    'curso_id', 'orden', 'tipo', 'curso_requisito_id',
    'nota_minima', 'minimo_acumulado', 'activo',
])]
class ReglaLevantamiento extends Model
{
    use HasFactory;

    /** Los cuatro tipos evaluables que define ES-01. */
    public const TIPO_NOTA_MINIMA = 'Requisito aprobado con nota mínima';
    public const TIPO_ACUMULADO = 'Créditos o cursos acumulados';
    public const TIPO_PLAN_TERMINAL = 'Pertenencia a plan terminal';
    public const TIPO_REVISION_MANUAL = 'Siempre revisión manual';

    protected function casts(): array
    {
        return [
            'nota_minima' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    /** @param Builder<ReglaLevantamiento> $query */
    public function scopeActivas(Builder $query): void
    {
        $query->where('activo', true);
    }

    /** El motor evalúa en el orden configurado por la Coordinadora. */
    /** @param Builder<ReglaLevantamiento> $query */
    public function scopeEnOrden(Builder $query): void
    {
        $query->orderBy('orden');
    }

    /** @return BelongsTo<Curso, $this> */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /** Parámetro del tipo (a): el requisito cuya nota se compara. */
    /** @return BelongsTo<Curso, $this> */
    public function cursoRequisito(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_requisito_id');
    }

    /** @return HasMany<Solicitud, $this> */
    public function solicitudesIncumplidas(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'regla_incumplida_id');
    }
}
