<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('convalidaciones_historicas')]
#[Fillable(['institucion', 'curso_externo', 'curso_id', 'resultado', 'numero_resolucion'])]
class ConvalidacionHistorica extends Model
{
    use HasFactory;

    /** Búsqueda de precedente de ES-02 por institución y curso externo. */
    /** @param Builder<ConvalidacionHistorica> $query */
    public function scopePrecedente(Builder $query, string $institucion, string $cursoExterno): void
    {
        $query->where('institucion', $institucion)
            ->where('curso_externo', $cursoExterno);
    }

    /** @param Builder<ConvalidacionHistorica> $query */
    public function scopeAprobadas(Builder $query): void
    {
        $query->where('resultado', 'Aprobada');
    }

    /** @return BelongsTo<Curso, $this> */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /** @return HasMany<Solicitud, $this> */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'convalidacion_historica_id');
    }
}
