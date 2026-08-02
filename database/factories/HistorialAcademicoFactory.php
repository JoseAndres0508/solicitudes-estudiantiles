<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\HistorialAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HistorialAcademico> */
class HistorialAcademicoFactory extends Factory
{
    protected $model = HistorialAcademico::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'curso_id' => Curso::factory(),
            'periodo_academico_id' => null,
            'estado' => 'Aprobado',
            'nota' => fake()->randomFloat(2, 70, 100),
            'equiparacion_id' => null,
        ];
    }

    public function aprobado(float $nota = 85.0): static
    {
        return $this->state(fn () => ['estado' => 'Aprobado', 'nota' => $nota]);
    }

    public function reprobado(): static
    {
        return $this->state(fn () => [
            'estado' => 'Reprobado',
            'nota' => fake()->randomFloat(2, 0, 69.99),
        ]);
    }

    /** Deja la huella que ES-01 consulta para detectar levantamientos duplicados. */
    public function requisitoLevantado(): static
    {
        return $this->state(fn () => ['estado' => 'Requisito levantado', 'nota' => null]);
    }

    public function acreditadoPorConvalidacion(): static
    {
        return $this->state(fn () => [
            'estado' => 'Acreditado por convalidación',
            'nota' => null,
        ]);
    }
}
