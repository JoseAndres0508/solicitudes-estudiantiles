<?php

namespace Database\Factories;

use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PeriodoAcademico> */
class PeriodoAcademicoFactory extends Factory
{
    protected $model = PeriodoAcademico::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $anio = fake()->numberBetween(2023, 2026);
        $cuatrimestre = fake()->numberBetween(1, 3);
        $inicio = "{$anio}-".str_pad((string) ($cuatrimestre * 4 - 3), 2, '0', STR_PAD_LEFT).'-01';

        return [
            'anio' => $anio,
            'cuatrimestre' => $cuatrimestre,
            'fecha_inicio' => $inicio,
            'fecha_fin' => date('Y-m-d', strtotime($inicio.' +3 months')),
        ];
    }
}
