<?php

namespace Database\Factories;

use App\Models\Carrera;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Carrera> */
class CarreraFactory extends Factory
{
    protected $model = Carrera::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nombre' => 'Ingeniería en '.fake()->unique()->words(3, true),
            'activa' => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(fn () => ['activa' => false]);
    }
}
