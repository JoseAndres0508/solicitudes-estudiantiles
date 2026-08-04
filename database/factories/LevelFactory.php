<?php

namespace Database\Factories;

use App\Models\Level;
use App\Models\StudyPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Level> */
class LevelFactory extends Factory
{
    protected $model = Level::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'study_plan_id' => StudyPlan::factory(),
            'number' => fake()->numberBetween(1, 12),
        ];
    }
}
