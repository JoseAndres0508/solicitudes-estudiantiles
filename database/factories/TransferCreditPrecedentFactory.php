<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\TransferCreditPrecedent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TransferCreditPrecedent> */
class TransferCreditPrecedentFactory extends Factory
{
    protected $model = TransferCreditPrecedent::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'institution' => fake()->randomElement([
                'Universidad de Costa Rica',
                'Instituto Tecnológico de Costa Rica',
                'Universidad Nacional',
                'Universidad Estatal a Distancia',
            ]),
            'external_course' => mb_strtoupper(fake()->words(3, true)),
            'course_id' => Course::factory(),
            'outcome' => TransferCreditPrecedent::OUTCOME_APPROVED,
            'resolution_number' => 'CTC-'.fake()->unique()->numberBetween(100, 999).'-2025',
        ];
    }

    public function denied(): static
    {
        return $this->state(fn () => ['outcome' => TransferCreditPrecedent::OUTCOME_DENIED]);
    }
}
