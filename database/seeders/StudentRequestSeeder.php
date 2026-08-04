<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentRequest;
use App\Models\StudentRequestStatusHistory;
use App\Models\TransferCreditPrecedent;
use App\Models\User;
use App\Models\WaiverRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Requests of both types and in several statuses, to populate the ES-04 inbox. */
class StudentRequestSeeder extends Seeder
{
    public function run(): void
    {
        $reviewer = $this->reviewer('docencia@utn.ac.cr', 'Bandeja de Docencia', 'Coordinadora de Docencia');
        $committee = $this->reviewer('comision@utn.ac.cr', 'Comisión Técnica', 'Comisión Técnica');

        $course = fn (string $c) => Course::firstWhere('code', $c);
        $student = fn (string $id) => Student::firstWhere('national_id', $id);

        // ES-01 — approved through the minimum grade rule.
        $ana = StudentRequest::create([
            'student_id' => $student('2-0801-0455')->id,
            'type' => StudentRequest::TYPE_WAIVER,
            'course_id' => $course('ITI-401')->id,
            'prerequisite_course_id' => $course('ITI-302')->id,
            'engine_result' => StudentRequest::ENGINE_APPROVED,
            'status' => StudentRequest::STATUS_APPROVED,
            'reviewer_id' => $reviewer->id,
            'estimated_resolution_date' => now()->subDays(2)->toDateString(),
        ]);
        $this->trace($ana, null, StudentRequest::STATUS_PENDING, $reviewer, null);
        $this->trace($ana, StudentRequest::STATUS_PENDING, StudentRequest::STATUS_APPROVED, $reviewer,
            'El motor confirmó nota 92 en ITI-302, sobre el mínimo de 85.');

        // ES-01 — denied: no rule concludes in favour.
        $rule = WaiverRule::where('course_id', $course('ITI-401')->id)
            ->where('evaluation_order', 1)->first();
        $bruno = StudentRequest::create([
            'student_id' => $student('1-1702-0388')->id,
            'type' => StudentRequest::TYPE_WAIVER,
            'course_id' => $course('ITI-401')->id,
            'prerequisite_course_id' => $course('ITI-302')->id,
            'engine_result' => StudentRequest::ENGINE_REJECTED,
            'failed_rule_id' => $rule?->id,
            'status' => StudentRequest::STATUS_DENIED,
            'reviewer_id' => $reviewer->id,
        ]);
        $this->trace($bruno, null, StudentRequest::STATUS_PENDING, $reviewer, null);
        $this->trace($bruno, StudentRequest::STATUS_PENDING, StudentRequest::STATUS_DENIED, $reviewer,
            'Nota 71 en ITI-302, por debajo del mínimo de 85; tampoco alcanza los 60 créditos.');

        // ES-01 — course without configured rules: escalates to manual review.
        $manual = StudentRequest::create([
            'student_id' => $student('2-0655-0912')->id,
            'type' => StudentRequest::TYPE_WAIVER,
            'course_id' => $course('ITI-501')->id,
            'prerequisite_course_id' => $course('ITI-402')->id,
            'engine_result' => StudentRequest::ENGINE_MANUAL_REVIEW,
            'status' => StudentRequest::STATUS_PENDING,
        ]);
        $this->trace($manual, null, StudentRequest::STATUS_PENDING, null, null);

        // ES-02 — with an approved precedent in the catalogue.
        $precedent = TransferCreditPrecedent::firstWhere('external_course', 'PROGRAMACIÓN I');
        $transfer = StudentRequest::create([
            'student_id' => $student('2-0655-0912')->id,
            'type' => StudentRequest::TYPE_TRANSFER_CREDIT,
            'course_id' => $course('ITI-101')->id,
            'source_institution' => 'Universidad de Costa Rica',
            'external_course' => 'PROGRAMACIÓN I',
            'transfer_credit_precedent_id' => $precedent?->id,
            'status' => StudentRequest::STATUS_UNDER_REVIEW,
            'reviewer_id' => $committee->id,
            'estimated_resolution_date' => now()->addWeekdays(5)->toDateString(),
        ]);
        $this->trace($transfer, null, StudentRequest::STATUS_PENDING, null, null);
        $this->trace($transfer, StudentRequest::STATUS_PENDING, StudentRequest::STATUS_UNDER_REVIEW,
            $committee, 'Precedente aprobado encontrado: resolución CTC-114-2024.');

        // ES-02 — no precedent in the catalogue.
        $noPrecedent = StudentRequest::create([
            'student_id' => $student('1-1702-0388')->id,
            'type' => StudentRequest::TYPE_TRANSFER_CREDIT,
            'course_id' => $course('ITI-502')->id,
            'source_institution' => 'Universidad Latina',
            'external_course' => 'INGENIERÍA DE SISTEMAS',
            'status' => StudentRequest::STATUS_PENDING,
        ]);
        $this->trace($noPrecedent, null, StudentRequest::STATUS_PENDING, null, null);

        // ES-03 — received over 24 h ago with no estimated date.
        $overdue = StudentRequest::create([
            'student_id' => $student('3-0499-0201')->id,
            'type' => StudentRequest::TYPE_WAIVER,
            'course_id' => $course('ITI-402')->id,
            'prerequisite_course_id' => $course('ITI-301')->id,
            'engine_result' => StudentRequest::ENGINE_MANUAL_REVIEW,
            'status' => StudentRequest::STATUS_UNDER_REVIEW,
            'reviewer_id' => $reviewer->id,
        ]);
        $overdue->forceFill(['created_at' => now()->subHours(30)])->save();
        $this->trace($overdue, null, StudentRequest::STATUS_PENDING, null, null);
    }

    private function reviewer(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password'), 'email_verified_at' => now()],
        );
        $user->roles()->syncWithPivotValues([Role::firstWhere('name', $role)->id], ['created_at' => now()]);

        return $user;
    }

    private function trace(StudentRequest $request, ?string $from, string $to, ?User $user, ?string $comment): void
    {
        StudentRequestStatusHistory::create([
            'student_request_id' => $request->id,
            'previous_status' => $from,
            'new_status' => $to,
            'comment' => $comment,
            'user_id' => $user?->id,
            'notified_at' => now(),
        ]);
    }
}
