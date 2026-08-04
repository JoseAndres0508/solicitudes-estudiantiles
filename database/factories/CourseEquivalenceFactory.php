<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseEquivalence;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CourseEquivalence> */
class CourseEquivalenceFactory extends Factory
{
    protected $model = CourseEquivalence::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'source_course_id' => Course::factory(),
            'target_course_id' => Course::factory(),
            'direction' => fake()->randomElement(['Anterior a nuevo', 'Nuevo a anterior', 'Bidireccional']),
            'resolution_number' => 'R-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'Vigente',
            'superseded_by_id' => null,
        ];
    }

    /** chk_course_equivalences_superseded requires the replacement when superseded. */
    public function superseded(int $replacementId): static
    {
        return $this->state(fn () => [
            'status' => 'Sustituida',
            'superseded_by_id' => $replacementId,
        ]);
    }
}
