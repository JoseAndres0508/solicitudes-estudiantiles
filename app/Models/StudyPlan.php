<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'degree_program_id', 'name', 'implementation_year',
    'classification', 'enrollment_closing_date',
])]
class StudyPlan extends Model
{
    use HasFactory;

    public const CLASSIFICATION_TERMINAL = 'Terminal';

    protected function casts(): array
    {
        return ['enrollment_closing_date' => 'date'];
    }

    /** Basis for rule (c) of ES-01: membership in a terminal plan. */
    public function isTerminal(): bool
    {
        return $this->classification === self::CLASSIFICATION_TERMINAL;
    }

    /** @return BelongsTo<DegreeProgram, $this> */
    public function degreeProgram(): BelongsTo
    {
        return $this->belongsTo(DegreeProgram::class);
    }

    /** @return HasMany<Level, $this> */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('number');
    }

    /** @return HasMany<Prerequisite, $this> */
    public function prerequisites(): HasMany
    {
        return $this->hasMany(Prerequisite::class);
    }

    /** @return BelongsToMany<Student, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_study_plan')
            ->withPivot(['current_level', 'created_at']);
    }
}
