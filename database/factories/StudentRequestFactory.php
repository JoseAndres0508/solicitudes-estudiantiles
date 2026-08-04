<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentRequest>
 *
 * chk_student_requests_waiver_fields and chk_student_requests_transfer_fields
 * require each type to carry its own mandatory columns.
 */
class StudentRequestFactory extends Factory
{
    protected $model = StudentRequest::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'type' => StudentRequest::TYPE_WAIVER,
            'course_id' => Course::factory(),
            'prerequisite_course_id' => Course::factory(),
            'source_institution' => null,
            'external_course' => null,
            'transfer_credit_precedent_id' => null,
            'engine_result' => null,
            'failed_rule_id' => null,
            'status' => StudentRequest::STATUS_PENDING,
            'estimated_resolution_date' => null,
            'reviewer_id' => null,
        ];
    }

    public function transferCredit(): static
    {
        return $this->state(fn () => [
            'type' => StudentRequest::TYPE_TRANSFER_CREDIT,
            'prerequisite_course_id' => null,
            'source_institution' => 'Universidad de Costa Rica',
            'external_course' => mb_strtoupper(fake()->words(3, true)),
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn () => [
            'status' => StudentRequest::STATUS_UNDER_REVIEW,
            'reviewer_id' => User::factory(),
            'estimated_resolution_date' => now()->addWeekdays(5)->toDateString(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => StudentRequest::STATUS_APPROVED,
            'reviewer_id' => User::factory(),
        ]);
    }

    public function denied(): static
    {
        return $this->state(fn () => [
            'status' => StudentRequest::STATUS_DENIED,
            'reviewer_id' => User::factory(),
        ]);
    }

    /** Received over 24 h ago with no estimated date: triggers the ES-03 rule. */
    public function overdueWithoutDate(): static
    {
        return $this->state(fn () => [
            'status' => StudentRequest::STATUS_UNDER_REVIEW,
            'estimated_resolution_date' => null,
            'created_at' => now()->subHours(30),
        ]);
    }
}
