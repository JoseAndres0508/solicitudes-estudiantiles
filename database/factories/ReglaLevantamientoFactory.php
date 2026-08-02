<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\ReglaLevantamiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReglaLevantamiento> */
class ReglaLevantamientoFactory extends Factory
{
    protected $model = ReglaLevantamiento::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'orden' => 1,
            'tipo' => ReglaLevantamiento::TIPO_REVISION_MANUAL,
            'curso_requisito_id' => null,
            'nota_minima' => null,
            'minimo_acumulado' => null,
            'activo' => true,
        ];
    }

    /** Tipo (a): requisito X aprobado con nota mayor o igual a N. */
    public function notaMinima(int $cursoRequisitoId, float $nota = 80.0, int $orden = 1): static
    {
        return $this->state(fn () => [
            'tipo' => ReglaLevantamiento::TIPO_NOTA_MINIMA,
            'curso_requisito_id' => $cursoRequisitoId,
            'nota_minima' => $nota,
            'orden' => $orden,
        ]);
    }

    /** Tipo (b): créditos o cursos acumulados mayores o iguales a K. */
    public function acumulado(int $minimo = 60, int $orden = 1): static
    {
        return $this->state(fn () => [
            'tipo' => ReglaLevantamiento::TIPO_ACUMULADO,
            'minimo_acumulado' => $minimo,
            'orden' => $orden,
        ]);
    }

    /** Tipo (c): pertenencia del estudiante a un plan terminal. */
    public function planTerminal(int $orden = 1): static
    {
        return $this->state(fn () => [
            'tipo' => ReglaLevantamiento::TIPO_PLAN_TERMINAL,
            'orden' => $orden,
        ]);
    }

    /** Tipo (d): siempre revisión manual. */
    public function revisionManual(int $orden = 1): static
    {
        return $this->state(fn () => [
            'tipo' => ReglaLevantamiento::TIPO_REVISION_MANUAL,
            'orden' => $orden,
        ]);
    }
}
