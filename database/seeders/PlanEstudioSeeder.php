<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Requisito;
use Illuminate\Database\Seeder;

/**
 * Malla curricular simulada de ITI: dos planes sobre la misma carrera.
 *
 * El Plan 2019 es Terminal y el Plan 2023 Vigente, lo que permite demostrar
 * la regla (c) de ES-01. Los créditos viven en curso_nivel, así que el mismo
 * curso vale distinto en cada plan: es el caso que motivó descartar
 * cursos.creditos (ver Entrada 15 del diario).
 */
class PlanEstudioSeeder extends Seeder
{
    /** codigo => [nombre, creditos plan 2023, creditos plan 2019, nivel] */
    private const CURSOS = [
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

    /** curso que exige => curso requerido */
    private const REQUISITOS = [
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
        $carrera = Carrera::firstWhere(
            'nombre',
            'Ingeniería en Tecnologías de Información - Tecnologías de Información'
        );

        $cursos = [];
        foreach (self::CURSOS as $codigo => [$nombre, , , ]) {
            $cursos[$codigo] = Curso::firstOrCreate(
                ['codigo' => $codigo],
                ['carrera_id' => $carrera->id, 'nombre' => $nombre],
            );
        }

        $planes = [
            'Plan 2023' => ['anio' => 2023, 'clasificacion' => 'Vigente', 'cierre' => null, 'col' => 1],
            'Plan 2019' => ['anio' => 2019, 'clasificacion' => 'Terminal', 'cierre' => '2027-12-31', 'col' => 2],
        ];

        foreach ($planes as $nombrePlan => $datos) {
            $plan = PlanEstudio::firstOrCreate(
                ['carrera_id' => $carrera->id, 'nombre' => $nombrePlan],
                [
                    'anio_implementacion' => $datos['anio'],
                    'clasificacion' => $datos['clasificacion'],
                    'fecha_cierre_matricula' => $datos['cierre'],
                ],
            );

            $niveles = [];
            foreach (range(1, 5) as $numero) {
                $niveles[$numero] = Nivel::firstOrCreate(
                    ['plan_estudio_id' => $plan->id, 'numero' => $numero],
                );
            }

            foreach (self::CURSOS as $codigo => [, $cred2023, $cred2019, $numeroNivel]) {
                $creditos = $datos['col'] === 1 ? $cred2023 : $cred2019;
                $niveles[$numeroNivel]->cursos()->syncWithoutDetaching([
                    $cursos[$codigo]->id => ['creditos' => $creditos, 'created_at' => now()],
                ]);
            }

            foreach (self::REQUISITOS as $exige => $requerido) {
                Requisito::firstOrCreate([
                    'plan_estudio_id' => $plan->id,
                    'curso_requerido_id' => $cursos[$requerido]->id,
                    'curso_exige_id' => $cursos[$exige]->id,
                ]);
            }
        }
    }
}
