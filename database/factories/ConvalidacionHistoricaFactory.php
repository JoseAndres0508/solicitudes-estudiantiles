<?php

namespace Database\Factories;

use App\Models\ConvalidacionHistorica;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConvalidacionHistorica> */
class ConvalidacionHistoricaFactory extends Factory
{
    protected $model = ConvalidacionHistorica::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'institucion' => fake()->randomElement([
                'Universidad de Costa Rica',
                'Instituto Tecnológico de Costa Rica',
                'Universidad Nacional',
                'Universidad Estatal a Distancia',
            ]),
            'curso_externo' => mb_strtoupper(fake()->words(3, true)),
            'curso_id' => Curso::factory(),
            'resultado' => 'Aprobada',
            'numero_resolucion' => 'CTC-'.fake()->unique()->numberBetween(100, 999).'-2025',
        ];
    }

    public function denegada(): static
    {
        return $this->state(fn () => ['resultado' => 'Denegada']);
    }
}
