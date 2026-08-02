<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'activa'])]
class Carrera extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    /** @return HasMany<Curso, $this> */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }

    /** @return HasMany<PlanEstudio, $this> */
    public function planesEstudio(): HasMany
    {
        return $this->hasMany(PlanEstudio::class);
    }
}
