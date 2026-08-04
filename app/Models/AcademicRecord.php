<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'course_id', 'academic_term_id', 'status', 'grade', 'course_equivalence_id'])]
class AcademicRecord extends Model
{
    use HasFactory;

    public const STATUS_PASSED = 'Aprobado';
    public const STATUS_FAILED = 'Reprobado';
    public const STATUS_EQUIVALENCE = 'Acreditado por equiparación';
    public const STATUS_TRANSFER_CREDIT = 'Acreditado por convalidación';
    public const STATUS_PREREQUISITE_WAIVED = 'Requisito levantado';

    /** Statuses the ES-01 engine counts as a cleared course. */
    public const CLEARED_STATUSES = [
        self::STATUS_PASSED,
        self::STATUS_EQUIVALENCE,
        self::STATUS_TRANSFER_CREDIT,
        self::STATUS_PREREQUISITE_WAIVED,
    ];

    protected function casts(): array
    {
        return ['grade' => 'decimal:2'];
    }

    /** @param Builder<AcademicRecord> $query */
    public function scopeCleared(Builder $query): void
    {
        $query->whereIn('status', self::CLEARED_STATUSES);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<AcademicTerm, $this> */
    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    /** @return BelongsTo<CourseEquivalence, $this> */
    public function courseEquivalence(): BelongsTo
    {
        return $this->belongsTo(CourseEquivalence::class);
    }
}
