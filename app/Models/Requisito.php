<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['plan_estudio_id', 'curso_requerido_id', 'curso_exige_id'])]
class Requisito extends Model
{
    use HasFactory;

    /** La tabla solo tiene created_at. */
    public const UPDATED_AT = null;

    /** @return BelongsTo<PlanEstudio, $this> */
    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class);
    }

    /** Curso que debe aprobarse primero. */
    /** @return BelongsTo<Curso, $this> */
    public function cursoRequerido(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_requerido_id');
    }

    /** Curso que exige el requisito. */
    /** @return BelongsTo<Curso, $this> */
    public function cursoExige(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_exige_id');
    }
}
