<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use Illuminate\Database\Seeder;

class AcademicTermSeeder extends Seeder
{
    /** @var list<array{int, int, string, string}> */
    private const TERMS = [
        [2025, 1, '2025-01-13', '2025-04-25'],
        [2025, 2, '2025-05-05', '2025-08-15'],
        [2025, 3, '2025-09-01', '2025-12-19'],
        [2026, 1, '2026-01-12', '2026-04-24'],
        [2026, 2, '2026-05-04', '2026-08-14'],
    ];

    public function run(): void
    {
        foreach (self::TERMS as [$year, $term, $start, $end]) {
            AcademicTerm::firstOrCreate(
                ['year' => $year, 'term_number' => $term],
                ['start_date' => $start, 'end_date' => $end],
            );
        }
    }
}
