<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id', 'evaluation_order', 'type', 'prerequisite_course_id',
    'minimum_grade', 'minimum_accumulated', 'is_active',
])]
class WaiverRule extends Model
{
    use HasFactory;

    /** The four evaluable rule types defined by ES-01. */
    public const TYPE_MINIMUM_GRADE = 'Requisito aprobado con nota mínima';
    public const TYPE_ACCUMULATED = 'Créditos o cursos acumulados';
    public const TYPE_TERMINAL_PLAN = 'Pertenencia a plan terminal';
    public const TYPE_MANUAL_REVIEW = 'Siempre revisión manual';

    protected function casts(): array
    {
        return ['minimum_grade' => 'decimal:2', 'is_active' => 'boolean'];
    }

    /** @param Builder<WaiverRule> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** The engine evaluates rules in the order configured by the coordinator. */
    /** @param Builder<WaiverRule> $query */
    public function scopeInEvaluationOrder(Builder $query): void
    {
        $query->orderBy('evaluation_order');
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** Parameter of type (a): the prerequisite whose grade is compared. */
    /** @return BelongsTo<Course, $this> */
    public function prerequisiteCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    /** @return HasMany<StudentRequest, $this> */
    public function failedRequests(): HasMany
    {
        return $this->hasMany(StudentRequest::class, 'failed_rule_id');
    }
}
