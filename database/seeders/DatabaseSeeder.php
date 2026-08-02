<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * El orden respeta las dependencias: catálogos institucionales primero,
     * luego la malla curricular, el expediente simulado y por último las
     * solicitudes que lo consumen.
     */
    public function run(): void
    {
        $this->call([
            RolPermisoSeeder::class,
            CarreraSeeder::class,
            PeriodoAcademicoSeeder::class,
            PlanEstudioSeeder::class,
            ReglaLevantamientoSeeder::class,
            ConvalidacionHistoricaSeeder::class,
            EstudianteSeeder::class,
            SolicitudSeeder::class,
        ]);
    }
}
