<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'national_id', 'first_name', 'last_name', 'second_last_name', 'is_active'])]
class Student extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name} {$this->second_last_name}");
    }

    /** Rule (c) of ES-01: the student is enrolled in at least one terminal plan. */
    public function isInTerminalPlan(): bool
    {
        return $this->studyPlans()
            ->where('classification', StudyPlan::CLASSIFICATION_TERMINAL)
            ->exists();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<StudyPlan, $this> */
    public function studyPlans(): BelongsToMany
    {
        return $this->belongsToMany(StudyPlan::class, 'student_study_plan')
            ->withPivot(['current_level', 'created_at']);
    }

    /** @return HasMany<AcademicRecord, $this> */
    public function academicRecords(): HasMany
    {
        return $this->hasMany(AcademicRecord::class);
    }

    /** @return HasMany<StudentRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(StudentRequest::class);
    }
}
