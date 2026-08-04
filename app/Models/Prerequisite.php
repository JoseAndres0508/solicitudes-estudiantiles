<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['study_plan_id', 'required_course_id', 'target_course_id'])]
class Prerequisite extends Model
{
    use HasFactory;

    /** The table only carries created_at. */
    public const UPDATED_AT = null;

    /** @return BelongsTo<StudyPlan, $this> */
    public function studyPlan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class);
    }

    /** Course that must be passed first. */
    /** @return BelongsTo<Course, $this> */
    public function requiredCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'required_course_id');
    }

    /** Course that demands the prerequisite. */
    /** @return BelongsTo<Course, $this> */
    public function targetCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'target_course_id');
    }
}
