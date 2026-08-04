<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'student_id', 'type', 'course_id', 'prerequisite_course_id',
    'source_institution', 'external_course', 'transfer_credit_precedent_id',
    'engine_result', 'failed_rule_id', 'status',
    'estimated_resolution_date', 'reviewer_id',
])]
class StudentRequest extends Model
{
    use HasFactory;

    public const TYPE_WAIVER = 'Levantamiento de requisito';
    public const TYPE_TRANSFER_CREDIT = 'Convalidación';

    public const STATUS_PENDING = 'Pendiente de revisión';
    public const STATUS_UNDER_REVIEW = 'En revisión';
    public const STATUS_APPROVED = 'Aprobada';
    public const STATUS_DENIED = 'Denegada';

    /** The three outcomes ES-01 requires the engine to return immediately. */
    public const ENGINE_APPROVED = 'Procede automáticamente';
    public const ENGINE_REJECTED = 'No procede';
    public const ENGINE_MANUAL_REVIEW = 'Requiere revisión manual';

    protected function casts(): array
    {
        return ['estimated_resolution_date' => 'date'];
    }

    /** ES-04 centralised inbox; backed by student_requests_inbox_index. */
    /** @param Builder<StudentRequest> $query */
    public function scopeInbox(Builder $query): void
    {
        $query->orderByDesc('created_at');
    }

    /** @param Builder<StudentRequest> $query */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** Course to enrol in, or internal course the student is applying for. */
    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** Unmet prerequisite; only on waiver requests. */
    /** @return BelongsTo<Course, $this> */
    public function prerequisiteCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    /** @return BelongsTo<TransferCreditPrecedent, $this> */
    public function transferCreditPrecedent(): BelongsTo
    {
        return $this->belongsTo(TransferCreditPrecedent::class);
    }

    /** @return BelongsTo<WaiverRule, $this> */
    public function failedRule(): BelongsTo
    {
        return $this->belongsTo(WaiverRule::class, 'failed_rule_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return HasMany<StudentRequestStatusHistory, $this> */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentRequestStatusHistory::class)->orderBy('created_at');
    }

    /** @return MorphMany<Attachment, $this> */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
