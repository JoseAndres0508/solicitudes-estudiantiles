<?php

namespace Database\Seeders;

use App\Models\ConvalidacionHistorica;
use App\Models\Curso;
use Illuminate\Database\Seeder;

/**
 * Catálogo de precedentes de ES-02.
 *
 * Incluye un precedente Aprobado y uno Denegado sobre instituciones distintas,
 * para poder demostrar los dos caminos del requerimiento.
 */
class ConvalidacionHistoricaSeeder extends Seeder
{
    /** @var list<array{string, string, string, string, string}> */
    private const PRECEDENTES = [
        ['Universidad de Costa Rica', 'PROGRAMACIÓN I', 'ITI-101', 'Aprobada', 'CTC-114-2024'],
        ['Universidad de Costa Rica', 'ESTRUCTURAS DE DATOS Y ALGORITMOS', 'ITI-301', 'Aprobada', 'CTC-118-2024'],
        ['Instituto Tecnológico de Costa Rica', 'BASES DE DATOS', 'ITI-302', 'Aprobada', 'CTC-203-2024'],
        ['Instituto Tecnológico de Costa Rica', 'MATEMÁTICA DISCRETA', 'ITI-224', 'Denegada', 'CTC-209-2024'],
        ['Universidad Nacional', 'DESARROLLO WEB', 'ITI-402', 'Denegada', 'CTC-311-2025'],
    ];

    public function run(): void
    {
        foreach (self::PRECEDENTES as [$institucion, $cursoExterno, $codigo, $resultado, $resolucion]) {
            ConvalidacionHistorica::firstOrCreate(
                ['institucion' => $institucion, 'curso_externo' => $cursoExterno],
                [
                    'curso_id' => Curso::firstWhere('codigo', $codigo)->id,
                    'resultado' => $resultado,
                    'numero_resolucion' => $resolucion,
                ],
            );
        }
    }
}
