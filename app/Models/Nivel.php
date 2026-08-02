<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table('niveles')]
#[Fillable(['plan_estudio_id', 'numero'])]
class Nivel extends Model
{
    use HasFactory;

    /** @return BelongsTo<PlanEstudio, $this> */
    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class);
    }

    /** @return BelongsToMany<Curso, $this> */
    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_nivel', 'nivel_id', 'curso_id')
            ->withPivot(['creditos', 'created_at']);
    }
}
