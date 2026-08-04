<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\WaiverRule;
use Illuminate\Database\Seeder;

/**
 * Waiver rules covering the four types defined by ES-01.
 *
 * ITI-501 is deliberately left with NO rules: it is the acceptance case
 * "a course without configured criteria escalates to manual review".
 *
 * These rows are data, not code. Deleting this seeder does not change how the
 * engine behaves; it only leaves the rule table empty.
 */
class WaiverRuleSeeder extends Seeder
{
    public function run(): void
    {
        $course = fn (string $code) => Course::firstWhere('code', $code);

        // ITI-401 — two chained rules: evaluation order decides the outcome.
        $this->rule($course('ITI-401'), 1, WaiverRule::TYPE_MINIMUM_GRADE, [
            'prerequisite_course_id' => $course('ITI-302')->id,
            'minimum_grade' => 85.00,
        ]);
        $this->rule($course('ITI-401'), 2, WaiverRule::TYPE_ACCUMULATED, [
            'minimum_accumulated' => 60,
        ]);

        // ITI-402 — accumulated credits only.
        $this->rule($course('ITI-402'), 1, WaiverRule::TYPE_ACCUMULATED, [
            'minimum_accumulated' => 45,
        ]);

        // ITI-502 — terminal plan, otherwise manual review.
        $this->rule($course('ITI-502'), 1, WaiverRule::TYPE_TERMINAL_PLAN);
        $this->rule($course('ITI-502'), 2, WaiverRule::TYPE_MANUAL_REVIEW);

        // ITI-301 — always manual review.
        $this->rule($course('ITI-301'), 1, WaiverRule::TYPE_MANUAL_REVIEW);
    }

    /** @param array<string, mixed> $parameters */
    private function rule(Course $course, int $order, string $type, array $parameters = []): void
    {
        WaiverRule::firstOrCreate(
            ['course_id' => $course->id, 'evaluation_order' => $order],
            array_merge(['type' => $type, 'is_active' => true], $parameters),
        );
    }
}
