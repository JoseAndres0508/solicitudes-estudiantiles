<?php

namespace Database\Factories;

use App\Models\AcademicTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AcademicTerm> */
class AcademicTermFactory extends Factory
{
    protected $model = AcademicTerm::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $year = fake()->numberBetween(2023, 2026);
        $term = fake()->numberBetween(1, 3);
        $start = sprintf('%d-%02d-01', $year, $term * 4 - 3);

        return [
            'year' => $year,
            'term_number' => $term,
            'start_date' => $start,
            'end_date' => date('Y-m-d', strtotime($start.' +3 months')),
        ];
    }
}
