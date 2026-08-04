<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\DegreeProgram;
use App\Models\Level;
use App\Models\Prerequisite;
use App\Models\StudyPlan;
use Illuminate\Database\Seeder;

/**
 * Simulated ITI curriculum: two plans over the same degree program.
 *
 * Plan 2019 is Terminal and Plan 2023 is current, which makes rule (c) of
 * ES-01 demonstrable. Credits live in course_level, so the same course is
 * worth a different amount in each plan — the case that ruled out putting
 * a credits column on courses.
 */
class StudyPlanSeeder extends Seeder
{
    /** code => [name, credits in Plan 2023, credits in Plan 2019, level] */
    private const COURSES = [
        'ITI-101' => ['INTRODUCCIÓN A LA PROGRAMACIÓN', 4, 3, 1],
        'ITI-102' => ['MATEMÁTICA PARA COMPUTACIÓN I', 3, 3, 1],
        'ITI-201' => ['PROGRAMACIÓN ORIENTADA A OBJETOS', 4, 4, 2],
        'ITI-224' => ['MATEMÁTICA PARA COMPUTACIÓN II', 4, 3, 2],
        'ITI-301' => ['ESTRUCTURAS DE DATOS', 4, 4, 3],
        'ITI-302' => ['BASES DE DATOS I', 4, 3, 3],
        'ITI-401' => ['BASES DE DATOS II', 4, 4, 4],
        'ITI-402' => ['PROGRAMACIÓN EN AMBIENTE WEB I', 4, 3, 4],
        'ITI-501' => ['PROGRAMACIÓN EN AMBIENTE WEB II', 4, 4, 5],
        'ITI-502' => ['INGENIERÍA DE SOFTWARE', 3, 3, 5],
    ];

    /** target course => required course */
    private const PREREQUISITES = [
        'ITI-201' => 'ITI-101',
        'ITI-224' => 'ITI-102',
        'ITI-301' => 'ITI-201',
        'ITI-302' => 'ITI-201',
        'ITI-401' => 'ITI-302',
        'ITI-402' => 'ITI-301',
        'ITI-501' => 'ITI-402',
        'ITI-502' => 'ITI-301',
    ];

    public function run(): void
    {
        $program = DegreeProgram::firstWhere(
            'name',
            'Ingeniería en Tecnologías de Información - Tecnologías de Información'
        );

        $courses = [];
        foreach (self::COURSES as $code => [$name, , , ]) {
            $courses[$code] = Course::firstOrCreate(
                ['code' => $code],
                ['degree_program_id' => $program->id, 'name' => $name],
            );
        }

        $plans = [
            'Plan 2023' => ['year' => 2023, 'classification' => 'Vigente', 'closing' => null, 'column' => 1],
            'Plan 2019' => ['year' => 2019, 'classification' => 'Terminal', 'closing' => '2027-12-31', 'column' => 2],
        ];

        foreach ($plans as $planName => $data) {
            $plan = StudyPlan::firstOrCreate(
                ['degree_program_id' => $program->id, 'name' => $planName],
                [
                    'implementation_year' => $data['year'],
                    'classification' => $data['classification'],
                    'enrollment_closing_date' => $data['closing'],
                ],
            );

            $levels = [];
            foreach (range(1, 5) as $number) {
                $levels[$number] = Level::firstOrCreate(
                    ['study_plan_id' => $plan->id, 'number' => $number],
                );
            }

            foreach (self::COURSES as $code => [, $credits2023, $credits2019, $levelNumber]) {
                $credits = $data['column'] === 1 ? $credits2023 : $credits2019;
                $levels[$levelNumber]->courses()->syncWithoutDetaching([
                    $courses[$code]->id => ['credits' => $credits, 'created_at' => now()],
                ]);
            }

            foreach (self::PREREQUISITES as $target => $required) {
                Prerequisite::firstOrCreate([
                    'study_plan_id' => $plan->id,
                    'required_course_id' => $courses[$required]->id,
                    'target_course_id' => $courses[$target]->id,
                ]);
            }
        }
    }
}
