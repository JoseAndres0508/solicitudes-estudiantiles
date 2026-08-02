<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\ReglaLevantamiento;
use Illuminate\Database\Seeder;

/**
 * Reglas de levantamiento que cubren los cuatro tipos de ES-01.
 *
 * ITI-501 queda deliberadamente SIN reglas: es el caso del criterio de
 * aceptación "curso sin criterios configurados se eleva a revisión manual".
 */
class ReglaLevantamientoSeeder extends Seeder
{
    public function run(): void
    {
        $curso = fn (string $codigo) => Curso::firstWhere('codigo', $codigo);

        // ITI-401 — dos reglas encadenadas: el orden decide el resultado.
        $this->regla($curso('ITI-401'), 1, ReglaLevantamiento::TIPO_NOTA_MINIMA, [
            'curso_requisito_id' => $curso('ITI-302')->id,
            'nota_minima' => 85.00,
        ]);
        $this->regla($curso('ITI-401'), 2, ReglaLevantamiento::TIPO_ACUMULADO, [
            'minimo_acumulado' => 60,
        ]);

        // ITI-402 — solo créditos acumulados.
        $this->regla($curso('ITI-402'), 1, ReglaLevantamiento::TIPO_ACUMULADO, [
            'minimo_acumulado' => 45,
        ]);

        // ITI-502 — plan terminal, y si no, revisión manual.
        $this->regla($curso('ITI-502'), 1, ReglaLevantamiento::TIPO_PLAN_TERMINAL);
        $this->regla($curso('ITI-502'), 2, ReglaLevantamiento::TIPO_REVISION_MANUAL);

        // ITI-301 — siempre revisión manual.
        $this->regla($curso('ITI-301'), 1, ReglaLevantamiento::TIPO_REVISION_MANUAL);
    }

    /** @param array<string, mixed> $parametros */
    private function regla(Curso $curso, int $orden, string $tipo, array $parametros = []): void
    {
        ReglaLevantamiento::firstOrCreate(
            ['curso_id' => $curso->id, 'orden' => $orden],
            array_merge(['tipo' => $tipo, 'activo' => true], $parametros),
        );
    }
}
