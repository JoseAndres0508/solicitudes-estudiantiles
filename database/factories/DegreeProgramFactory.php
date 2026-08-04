<?php

namespace Database\Factories;

use App\Models\DegreeProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DegreeProgram> */
class DegreeProgramFactory extends Factory
{
    protected $model = DegreeProgram::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => 'Ingeniería en '.fake()->unique()->words(3, true),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
