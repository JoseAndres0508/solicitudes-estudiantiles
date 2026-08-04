<?php

namespace Database\Factories;

use App\Models\DegreeProgram;
use App\Models\StudyPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudyPlan> */
class StudyPlanFactory extends Factory
{
    protected $model = StudyPlan::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $year = fake()->numberBetween(2018, 2025);

        return [
            'degree_program_id' => DegreeProgram::factory(),
            'name' => "Plan {$year}",
            'implementation_year' => $year,
            'classification' => 'Vigente',
            'enrollment_closing_date' => null,
        ];
    }

    /** chk_study_plans_terminal_date requires the closing date on terminal plans. */
    public function terminal(): static
    {
        return $this->state(fn () => [
            'classification' => StudyPlan::CLASSIFICATION_TERMINAL,
            'enrollment_closing_date' => fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
        ]);
    }
}
