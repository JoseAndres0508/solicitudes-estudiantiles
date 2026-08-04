<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['institution', 'external_course', 'course_id', 'outcome', 'resolution_number'])]
class TransferCreditPrecedent extends Model
{
    use HasFactory;

    public const OUTCOME_APPROVED = 'Aprobada';
    public const OUTCOME_DENIED = 'Denegada';

    /** ES-02 precedent lookup by institution and external course name. */
    /** @param Builder<TransferCreditPrecedent> $query */
    public function scopeMatching(Builder $query, string $institution, string $externalCourse): void
    {
        $query->where('institution', $institution)
            ->where('external_course', $externalCourse);
    }

    /** @param Builder<TransferCreditPrecedent> $query */
    public function scopeApproved(Builder $query): void
    {
        $query->where('outcome', self::OUTCOME_APPROVED);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return HasMany<StudentRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(StudentRequest::class);
    }
}
