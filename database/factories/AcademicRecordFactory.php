<?php

namespace Database\Factories;

use App\Models\AcademicRecord;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AcademicRecord> */
class AcademicRecordFactory extends Factory
{
    protected $model = AcademicRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'academic_term_id' => null,
            'status' => AcademicRecord::STATUS_PASSED,
            'grade' => fake()->randomFloat(2, 70, 100),
            'course_equivalence_id' => null,
        ];
    }

    public function passed(float $grade = 85.0): static
    {
        return $this->state(fn () => ['status' => AcademicRecord::STATUS_PASSED, 'grade' => $grade]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => AcademicRecord::STATUS_FAILED,
            'grade' => fake()->randomFloat(2, 0, 69.99),
        ]);
    }

    /** Leaves the trace ES-01 checks when detecting duplicate waiver requests. */
    public function prerequisiteWaived(): static
    {
        return $this->state(fn () => [
            'status' => AcademicRecord::STATUS_PREREQUISITE_WAIVED,
            'grade' => null,
        ]);
    }

    public function transferCredited(): static
    {
        return $this->state(fn () => [
            'status' => AcademicRecord::STATUS_TRANSFER_CREDIT,
            'grade' => null,
        ]);
    }
}
