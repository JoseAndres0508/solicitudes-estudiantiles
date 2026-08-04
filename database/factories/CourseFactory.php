<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\DegreeProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Course> */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'degree_program_id' => DegreeProgram::factory(),
            'code' => 'ITI-'.fake()->unique()->numberBetween(100, 999),
            'name' => mb_strtoupper(fake()->words(3, true)),
            'is_service_course' => false,
            'is_bottleneck' => false,
            'requires_lab' => false,
            'lab_type' => null,
            'is_active' => true,
        ];
    }

    /** Cross-faculty course: degree_program_id must be NULL per chk_courses_service_program. */
    public function serviceCourse(): static
    {
        return $this->state(fn () => [
            'degree_program_id' => null,
            'is_service_course' => true,
        ]);
    }

    /** chk_courses_lab_type requires the type whenever requires_lab is set. */
    public function withLab(string $type = 'Laboratorio de cómputo'): static
    {
        return $this->state(fn () => ['requires_lab' => true, 'lab_type' => $type]);
    }
}
