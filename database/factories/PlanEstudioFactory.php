<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlanEstudio> */
class PlanEstudioFactory extends Factory
{
    protected $model = PlanEstudio::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $anio = fake()->numberBetween(2018, 2025);

        return [
            'carrera_id' => Carrera::factory(),
            'nombre' => "Plan {$anio}",
            'anio_implementacion' => $anio,
            'clasificacion' => 'Vigente',
            'fecha_cierre_matricula' => null,
        ];
    }

    /** Plan terminal: la fecha de cierre es obligatoria (chk_planes_terminal_fecha). */
    public function terminal(): static
    {
        return $this->state(fn () => [
            'clasificacion' => 'Terminal',
            'fecha_cierre_matricula' => fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
        ]);
    }
}
