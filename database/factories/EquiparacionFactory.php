<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Equiparacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Equiparacion> */
class EquiparacionFactory extends Factory
{
    protected $model = Equiparacion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'curso_origen_id' => Curso::factory(),
            'curso_destino_id' => Curso::factory(),
            'sentido' => fake()->randomElement(['Anterior a nuevo', 'Nuevo a anterior', 'Bidireccional']),
            'numero_resolucion' => 'R-'.fake()->unique()->numberBetween(1000, 9999),
            'estado' => 'Vigente',
            'sustituida_por_id' => null,
        ];
    }
}
