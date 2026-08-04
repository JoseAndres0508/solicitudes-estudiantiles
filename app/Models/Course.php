<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'degree_program_id', 'code', 'name', 'is_service_course',
    'is_bottleneck', 'requires_lab', 'lab_type', 'is_active',
])]
class Course extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_service_course' => 'boolean',
            'is_bottleneck' => 'boolean',
            'requires_lab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<DegreeProgram, $this> */
    public function degreeProgram(): BelongsTo
    {
        return $this->belongsTo(DegreeProgram::class);
    }

    /** Plan levels the course belongs to, carrying its credit value in each one. */
    /** @return BelongsToMany<Level, $this> */
    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'course_level')
            ->withPivot(['credits', 'created_at']);
    }

    /** Prerequisites this course demands before enrolment. */
    /** @return HasMany<Prerequisite, $this> */
    public function prerequisites(): HasMany
    {
        return $this->hasMany(Prerequisite::class, 'target_course_id');
    }

    /** Courses that list this one as their prerequisite. */
    /** @return HasMany<Prerequisite, $this> */
    public function requiredBy(): HasMany
    {
        return $this->hasMany(Prerequisite::class, 'required_course_id');
    }

    /** @return HasMany<WaiverRule, $this> */
    public function waiverRules(): HasMany
    {
        return $this->hasMany(WaiverRule::class)->orderBy('evaluation_order');
    }

    /** @return HasMany<AcademicRecord, $this> */
    public function academicRecords(): HasMany
    {
        return $this->hasMany(AcademicRecord::class);
    }

    /** @return HasMany<TransferCreditPrecedent, $this> */
    public function transferCreditPrecedents(): HasMany
    {
        return $this->hasMany(TransferCreditPrecedent::class);
    }
}
