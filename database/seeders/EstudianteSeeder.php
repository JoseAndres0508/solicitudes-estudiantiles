<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\HistorialAcademico;
use App\Models\PeriodoAcademico;
use App\Models\PlanEstudio;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Expediente académico simulado (ES-01).
 *
 * Cada estudiante está construido para ejercitar un camino distinto del motor:
 *   - ana@utn      : cumple la regla de nota mínima  -> Procede automáticamente
 *   - bruno@utn    : nota insuficiente y pocos créditos -> No procede
 *   - carla@utn    : plan terminal                   -> Procede por la regla (c)
 *   - diego@utn    : ya tiene el requisito levantado -> caso de duplicado
 */
class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $rolEstudiante = Rol::firstWhere('name', 'Estudiante');
        $plan2023 = PlanEstudio::firstWhere('nombre', 'Plan 2023');
        $plan2019 = PlanEstudio::firstWhere('nombre', 'Plan 2019');
        $periodo = PeriodoAcademico::firstWhere(['anio' => 2025, 'cuatrimestre' => 3]);
        $curso = fn (string $codigo) => Curso::firstWhere('codigo', $codigo);

        $fichas = [
            [
                'cedula' => '2-0801-0455', 'nombre' => 'Ana', 'primer_apellido' => 'Rojas',
                'segundo_apellido' => 'Vargas', 'correo' => 'ana@utn.ac.cr',
                'plan' => $plan2023, 'nivel' => 4,
                // Aprobó ITI-302 con 92: supera la nota mínima de 85 de la regla 1 de ITI-401.
                'historial' => [
                    ['ITI-101', 'Aprobado', 88.0], ['ITI-102', 'Aprobado', 79.0],
                    ['ITI-201', 'Aprobado', 91.0], ['ITI-224', 'Aprobado', 84.0],
                    ['ITI-301', 'Aprobado', 86.0], ['ITI-302', 'Aprobado', 92.0],
                ],
            ],
            [
                'cedula' => '1-1702-0388', 'nombre' => 'Bruno', 'primer_apellido' => 'Méndez',
                'segundo_apellido' => 'Solano', 'correo' => 'bruno@utn.ac.cr',
                'plan' => $plan2023, 'nivel' => 3,
                // ITI-302 con 71: no llega a 85, y solo acumula 15 créditos.
                'historial' => [
                    ['ITI-101', 'Aprobado', 76.0], ['ITI-102', 'Aprobado', 72.0],
                    ['ITI-201', 'Aprobado', 70.0], ['ITI-302', 'Aprobado', 71.0],
                    ['ITI-224', 'Reprobado', 58.0],
                ],
            ],
            [
                'cedula' => '2-0655-0912', 'nombre' => 'Carla', 'primer_apellido' => 'Jiménez',
                'segundo_apellido' => 'Araya', 'correo' => 'carla@utn.ac.cr',
                'plan' => $plan2019, 'nivel' => 5,
                'historial' => [
                    ['ITI-101', 'Aprobado', 83.0], ['ITI-102', 'Aprobado', 80.0],
                    ['ITI-201', 'Aprobado', 78.0], ['ITI-224', 'Aprobado', 81.0],
                    ['ITI-301', 'Aprobado', 77.0],
                ],
            ],
            [
                'cedula' => '3-0499-0201', 'nombre' => 'Diego', 'primer_apellido' => 'Castro',
                'segundo_apellido' => null, 'correo' => 'diego@utn.ac.cr',
                'plan' => $plan2023, 'nivel' => 4,
                // ITI-302 ya figura como "Requisito levantado": presentar otra
                // solicitud sobre el mismo requisito debe responder duplicado.
                'historial' => [
                    ['ITI-101', 'Aprobado', 85.0], ['ITI-102', 'Aprobado', 82.0],
                    ['ITI-201', 'Aprobado', 80.0], ['ITI-302', 'Requisito levantado', null],
                ],
            ],
        ];

        foreach ($fichas as $ficha) {
            $user = User::firstOrCreate(
                ['email' => $ficha['correo']],
                [
                    'name' => "{$ficha['nombre']} {$ficha['primer_apellido']}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
            $user->roles()->syncWithPivotValues([$rolEstudiante->id], ['created_at' => now()]);

            $estudiante = Estudiante::firstOrCreate(
                ['cedula' => $ficha['cedula']],
                [
                    'user_id' => $user->id,
                    'nombre' => $ficha['nombre'],
                    'primer_apellido' => $ficha['primer_apellido'],
                    'segundo_apellido' => $ficha['segundo_apellido'],
                    'activo' => true,
                ],
            );

            $estudiante->planesEstudio()->syncWithPivotValues(
                [$ficha['plan']->id],
                ['nivel_actual' => $ficha['nivel'], 'created_at' => now()],
            );

            foreach ($ficha['historial'] as [$codigo, $estado, $nota]) {
                HistorialAcademico::firstOrCreate(
                    ['estudiante_id' => $estudiante->id, 'curso_id' => $curso($codigo)->id],
                    ['periodo_academico_id' => $periodo->id, 'estado' => $estado, 'nota' => $nota],
                );
            }
        }
    }
}
