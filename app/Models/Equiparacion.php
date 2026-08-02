<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('equiparaciones')]
#[Fillable([
    'curso_origen_id', 'curso_destino_id', 'sentido',
    'numero_resolucion', 'estado', 'sustituida_por_id',
])]
class Equiparacion extends Model
{
    use HasFactory;

    /** @return BelongsTo<Curso, $this> */
    public function cursoOrigen(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_origen_id');
    }

    /** @return BelongsTo<Curso, $this> */
    public function cursoDestino(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_destino_id');
    }

    /** @return BelongsTo<Equiparacion, $this> */
    public function sustituidaPor(): BelongsTo
    {
        return $this->belongsTo(Equiparacion::class, 'sustituida_por_id');
    }

    /** @return HasMany<HistorialAcademico, $this> */
    public function historialAcademico(): HasMany
    {
        return $this->hasMany(HistorialAcademico::class);
    }
}
