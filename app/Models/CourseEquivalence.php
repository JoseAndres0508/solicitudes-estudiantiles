<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'source_course_id', 'target_course_id', 'direction',
    'resolution_number', 'status', 'superseded_by_id',
])]
class CourseEquivalence extends Model
{
    use HasFactory;

    /** @return BelongsTo<Course, $this> */
    public function sourceCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'source_course_id');
    }

    /** @return BelongsTo<Course, $this> */
    public function targetCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'target_course_id');
    }

    /** @return BelongsTo<CourseEquivalence, $this> */
    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(CourseEquivalence::class, 'superseded_by_id');
    }

    /** @return HasMany<AcademicRecord, $this> */
    public function academicRecords(): HasMany
    {
        return $this->hasMany(AcademicRecord::class);
    }
}
