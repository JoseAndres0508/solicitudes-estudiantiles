<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Curso> */
class CursoFactory extends Factory
{
    protected $model = Curso::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'carrera_id' => Carrera::factory(),
            'codigo' => 'ITI-'.fake()->unique()->numberBetween(100, 999),
            'nombre' => mb_strtoupper(fake()->words(3, true)),
            'es_servicio' => false,
            'es_cuello_botella' => false,
            'requiere_laboratorio' => false,
            'tipo_laboratorio' => null,
            'activo' => true,
        ];
    }

    /** Curso transversal: carrera_id queda NULL, como exige chk_cursos_servicio_carrera. */
    public function servicio(): static
    {
        return $this->state(fn () => [
            'carrera_id' => null,
            'es_servicio' => true,
        ]);
    }

    public function conLaboratorio(): static
    {
        return $this->state(fn () => [
            'requiere_laboratorio' => true,
            'tipo_laboratorio' => 'Laboratorio de cómputo',
        ]);
    }
}
