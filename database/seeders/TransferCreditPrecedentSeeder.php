<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\TransferCreditPrecedent;
use Illuminate\Database\Seeder;

/**
 * Precedent catalogue for ES-02.
 *
 * Includes both approved and denied precedents across different institutions,
 * so the two branches of the requirement can be demonstrated.
 */
class TransferCreditPrecedentSeeder extends Seeder
{
    /** @var list<array{string, string, string, string, string}> */
    private const PRECEDENTS = [
        ['Universidad de Costa Rica', 'PROGRAMACIÓN I', 'ITI-101', 'Aprobada', 'CTC-114-2024'],
        ['Universidad de Costa Rica', 'ESTRUCTURAS DE DATOS Y ALGORITMOS', 'ITI-301', 'Aprobada', 'CTC-118-2024'],
        ['Instituto Tecnológico de Costa Rica', 'BASES DE DATOS', 'ITI-302', 'Aprobada', 'CTC-203-2024'],
        ['Instituto Tecnológico de Costa Rica', 'MATEMÁTICA DISCRETA', 'ITI-224', 'Denegada', 'CTC-209-2024'],
        ['Universidad Nacional', 'DESARROLLO WEB', 'ITI-402', 'Denegada', 'CTC-311-2025'],
    ];

    public function run(): void
    {
        foreach (self::PRECEDENTS as [$institution, $externalCourse, $code, $outcome, $resolution]) {
            TransferCreditPrecedent::firstOrCreate(
                ['institution' => $institution, 'external_course' => $externalCourse],
                [
                    'course_id' => Course::firstWhere('code', $code)->id,
                    'outcome' => $outcome,
                    'resolution_number' => $resolution,
                ],
            );
        }
    }
}
