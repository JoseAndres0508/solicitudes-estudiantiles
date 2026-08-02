<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('historial_academico')]
#[Fillable(['estudiante_id', 'curso_id', 'periodo_academico_id', 'estado', 'nota', 'equiparacion_id'])]
class HistorialAcademico extends Model
{
    use HasFactory;

    /** Estados que cuentan como curso superado para el motor de ES-01. */
    public const ESTADOS_SUPERADOS = [
        'Aprobado',
        'Acreditado por equiparación',
        'Acreditado por convalidación',
        'Requisito levantado',
    ];

    protected function casts(): array
    {
        return ['nota' => 'decimal:2'];
    }

    /** @param Builder<HistorialAcademico> $query */
    public function scopeSuperados(Builder $query): void
    {
        $query->whereIn('estado', self::ESTADOS_SUPERADOS);
    }

    /** @return BelongsTo<Estudiante, $this> */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /** @return BelongsTo<Curso, $this> */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /** @return BelongsTo<PeriodoAcademico, $this> */
    public function periodoAcademico(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    /** @return BelongsTo<Equiparacion, $this> */
    public function equiparacion(): BelongsTo
    {
        return $this->belongsTo(Equiparacion::class);
    }
}
