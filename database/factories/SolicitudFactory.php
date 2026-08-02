<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Solicitud> */
class SolicitudFactory extends Factory
{
    protected $model = Solicitud::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            'tipo' => Solicitud::TIPO_LEVANTAMIENTO,
            'curso_id' => Curso::factory(),
            'curso_requisito_id' => Curso::factory(),
            'institucion_origen' => null,
            'curso_externo' => null,
            'convalidacion_historica_id' => null,
            'resultado_motor' => null,
            'regla_incumplida_id' => null,
            'estado' => Solicitud::ESTADO_PENDIENTE,
            'fecha_estimada_resolucion' => null,
            'revisor_id' => null,
        ];
    }

    public function convalidacion(): static
    {
        return $this->state(fn () => [
            'tipo' => Solicitud::TIPO_CONVALIDACION,
            'curso_requisito_id' => null,
            'institucion_origen' => 'Universidad de Costa Rica',
            'curso_externo' => mb_strtoupper(fake()->words(3, true)),
        ]);
    }

    public function enRevision(): static
    {
        return $this->state(fn () => [
            'estado' => Solicitud::ESTADO_EN_REVISION,
            'revisor_id' => User::factory(),
            'fecha_estimada_resolucion' => now()->addWeekdays(5)->toDateString(),
        ]);
    }

    public function aprobada(): static
    {
        return $this->state(fn () => [
            'estado' => Solicitud::ESTADO_APROBADA,
            'revisor_id' => User::factory(),
        ]);
    }

    public function denegada(): static
    {
        return $this->state(fn () => [
            'estado' => Solicitud::ESTADO_DENEGADA,
            'revisor_id' => User::factory(),
        ]);
    }

    /** Sin fecha estimada y recibida hace más de 24 h: dispara la regla de ES-03. */
    public function sinFechaVencida(): static
    {
        return $this->state(fn () => [
            'estado' => Solicitud::ESTADO_EN_REVISION,
            'fecha_estimada_resolucion' => null,
            'created_at' => now()->subHours(30),
        ]);
    }
}
