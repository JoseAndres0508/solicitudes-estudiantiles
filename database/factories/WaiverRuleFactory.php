<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\WaiverRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaiverRule>
 *
 * chk_waiver_rules_parameters enforces that each type carries exactly its own
 * parameters, so every state below clears the ones that do not apply.
 */
class WaiverRuleFactory extends Factory
{
    protected $model = WaiverRule::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'evaluation_order' => 1,
            'type' => WaiverRule::TYPE_MANUAL_REVIEW,
            'prerequisite_course_id' => null,
            'minimum_grade' => null,
            'minimum_accumulated' => null,
            'is_active' => true,
        ];
    }

    /** Type (a): prerequisite X passed with a grade of at least N. */
    public function minimumGrade(int $prerequisiteCourseId, float $grade = 80.0, int $order = 1): static
    {
        return $this->state(fn () => [
            'type' => WaiverRule::TYPE_MINIMUM_GRADE,
            'prerequisite_course_id' => $prerequisiteCourseId,
            'minimum_grade' => $grade,
            'minimum_accumulated' => null,
            'evaluation_order' => $order,
        ]);
    }

    /** Type (b): accumulated credits or courses of at least K. */
    public function accumulated(int $minimum = 60, int $order = 1): static
    {
        return $this->state(fn () => [
            'type' => WaiverRule::TYPE_ACCUMULATED,
            'minimum_accumulated' => $minimum,
            'prerequisite_course_id' => null,
            'minimum_grade' => null,
            'evaluation_order' => $order,
        ]);
    }

    /** Type (c): the student belongs to a terminal study plan. */
    public function terminalPlan(int $order = 1): static
    {
        return $this->state(fn () => [
            'type' => WaiverRule::TYPE_TERMINAL_PLAN,
            'prerequisite_course_id' => null,
            'minimum_grade' => null,
            'minimum_accumulated' => null,
            'evaluation_order' => $order,
        ]);
    }

    /** Type (d): always escalate to manual review. */
    public function manualReview(int $order = 1): static
    {
        return $this->state(fn () => [
            'type' => WaiverRule::TYPE_MANUAL_REVIEW,
            'prerequisite_course_id' => null,
            'minimum_grade' => null,
            'minimum_accumulated' => null,
            'evaluation_order' => $order,
        ]);
    }
}
