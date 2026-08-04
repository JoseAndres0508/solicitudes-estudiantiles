<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The order follows the dependency chain: institutional catalogues first,
     * then the curriculum, the simulated records, and finally the requests
     * that consume them.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DegreeProgramSeeder::class,
            AcademicTermSeeder::class,
            StudyPlanSeeder::class,
            WaiverRuleSeeder::class,
            TransferCreditPrecedentSeeder::class,
            StudentSeeder::class,
            StudentRequestSeeder::class,
        ]);
    }
}
