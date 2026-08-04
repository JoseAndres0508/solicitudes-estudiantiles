<?php

namespace Database\Factories;

use App\Models\StudentRequest;
use App\Models\StudentRequestStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudentRequestStatusHistory> */
class StudentRequestStatusHistoryFactory extends Factory
{
    protected $model = StudentRequestStatusHistory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'student_request_id' => StudentRequest::factory(),
            'previous_status' => StudentRequest::STATUS_PENDING,
            'new_status' => StudentRequest::STATUS_UNDER_REVIEW,
            'comment' => null,
            'user_id' => User::factory(),
            'notified_at' => now(),
        ];
    }

    /** ES-03 requires a written reason on denials. */
    public function denial(string $reason): static
    {
        return $this->state(fn () => [
            'previous_status' => StudentRequest::STATUS_UNDER_REVIEW,
            'new_status' => StudentRequest::STATUS_DENIED,
            'comment' => $reason,
        ]);
    }

    public function notNotified(): static
    {
        return $this->state(fn () => ['notified_at' => null]);
    }
}
