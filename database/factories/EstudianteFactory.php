<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Estudiante> */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'cedula' => fake()->unique()->numerify('#-####-####'),
            'nombre' => fake()->firstName(),
            'primer_apellido' => fake()->lastName(),
            'segundo_apellido' => fake()->lastName(),
            'activo' => true,
        ];
    }

    /** Estudiante con cuenta de acceso al portal. */
    public function conCuenta(): static
    {
        return $this->state(fn () => ['user_id' => User::factory()]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
