<?php

namespace Database\Seeders;

use App\Models\AcademicRecord;
use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Simulated academic records for ES-01.
 *
 * Each student is built to exercise a different path through the engine:
 *   - ana@utn   : meets the minimum grade rule   -> proceeds automatically
 *   - bruno@utn : grade too low, few credits     -> does not proceed
 *   - carla@utn : enrolled in a terminal plan    -> proceeds through rule (c)
 *   - diego@utn : prerequisite already waived    -> duplicate request case
 */
class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentRole = Role::firstWhere('name', 'Estudiante');
        $plan2023 = StudyPlan::firstWhere('name', 'Plan 2023');
        $plan2019 = StudyPlan::firstWhere('name', 'Plan 2019');
        $term = AcademicTerm::firstWhere(['year' => 2025, 'term_number' => 3]);
        $course = fn (string $code) => Course::firstWhere('code', $code);

        $profiles = [
            [
                'national_id' => '2-0801-0455', 'first_name' => 'Ana', 'last_name' => 'Rojas',
                'second_last_name' => 'Vargas', 'email' => 'ana@utn.ac.cr',
                'plan' => $plan2023, 'level' => 4,
                // Passed ITI-302 with 92, above the 85 minimum of rule 1 on ITI-401.
                'records' => [
                    ['ITI-101', AcademicRecord::STATUS_PASSED, 88.0],
                    ['ITI-102', AcademicRecord::STATUS_PASSED, 79.0],
                    ['ITI-201', AcademicRecord::STATUS_PASSED, 91.0],
                    ['ITI-224', AcademicRecord::STATUS_PASSED, 84.0],
                    ['ITI-301', AcademicRecord::STATUS_PASSED, 86.0],
                    ['ITI-302', AcademicRecord::STATUS_PASSED, 92.0],
                ],
            ],
            [
                'national_id' => '1-1702-0388', 'first_name' => 'Bruno', 'last_name' => 'Méndez',
                'second_last_name' => 'Solano', 'email' => 'bruno@utn.ac.cr',
                'plan' => $plan2023, 'level' => 3,
                // ITI-302 with 71: below 85, and only 15 accumulated credits.
                'records' => [
                    ['ITI-101', AcademicRecord::STATUS_PASSED, 76.0],
                    ['ITI-102', AcademicRecord::STATUS_PASSED, 72.0],
                    ['ITI-201', AcademicRecord::STATUS_PASSED, 70.0],
                    ['ITI-302', AcademicRecord::STATUS_PASSED, 71.0],
                    ['ITI-224', AcademicRecord::STATUS_FAILED, 58.0],
                ],
            ],
            [
                'national_id' => '2-0655-0912', 'first_name' => 'Carla', 'last_name' => 'Jiménez',
                'second_last_name' => 'Araya', 'email' => 'carla@utn.ac.cr',
                'plan' => $plan2019, 'level' => 5,
                'records' => [
                    ['ITI-101', AcademicRecord::STATUS_PASSED, 83.0],
                    ['ITI-102', AcademicRecord::STATUS_PASSED, 80.0],
                    ['ITI-201', AcademicRecord::STATUS_PASSED, 78.0],
                    ['ITI-224', AcademicRecord::STATUS_PASSED, 81.0],
                    ['ITI-301', AcademicRecord::STATUS_PASSED, 77.0],
                ],
            ],
            [
                'national_id' => '3-0499-0201', 'first_name' => 'Diego', 'last_name' => 'Castro',
                'second_last_name' => null, 'email' => 'diego@utn.ac.cr',
                'plan' => $plan2023, 'level' => 4,
                // ITI-302 already recorded as waived: a second request on the
                // same prerequisite must be rejected as a duplicate.
                'records' => [
                    ['ITI-101', AcademicRecord::STATUS_PASSED, 85.0],
                    ['ITI-102', AcademicRecord::STATUS_PASSED, 82.0],
                    ['ITI-201', AcademicRecord::STATUS_PASSED, 80.0],
                    ['ITI-302', AcademicRecord::STATUS_PREREQUISITE_WAIVED, null],
                ],
            ],
        ];

        foreach ($profiles as $profile) {
            $user = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => "{$profile['first_name']} {$profile['last_name']}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
            $user->roles()->syncWithPivotValues([$studentRole->id], ['created_at' => now()]);

            $student = Student::firstOrCreate(
                ['national_id' => $profile['national_id']],
                [
                    'user_id' => $user->id,
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'second_last_name' => $profile['second_last_name'],
                    'is_active' => true,
                ],
            );

            $student->studyPlans()->syncWithPivotValues(
                [$profile['plan']->id],
                ['current_level' => $profile['level'], 'created_at' => now()],
            );

            foreach ($profile['records'] as [$code, $status, $grade]) {
                AcademicRecord::firstOrCreate(
                    ['student_id' => $student->id, 'course_id' => $course($code)->id],
                    ['academic_term_id' => $term->id, 'status' => $status, 'grade' => $grade],
                );
            }
        }
    }
}
