<?php

namespace Database\Factories;

use App\Models\Solicitud;
use App\Models\SolicitudEstadoHistorial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SolicitudEstadoHistorial> */
class SolicitudEstadoHistorialFactory extends Factory
{
    protected $model = SolicitudEstadoHistorial::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'solicitud_id' => Solicitud::factory(),
            'estado_anterior' => Solicitud::ESTADO_PENDIENTE,
            'estado_nuevo' => Solicitud::ESTADO_EN_REVISION,
            'comentario' => null,
            'user_id' => User::factory(),
            'notificado_at' => now(),
        ];
    }

    /** ES-03: la denegación exige comentario. */
    public function denegacion(string $motivo): static
    {
        return $this->state(fn () => [
            'estado_anterior' => Solicitud::ESTADO_EN_REVISION,
            'estado_nuevo' => Solicitud::ESTADO_DENEGADA,
            'comentario' => $motivo,
        ]);
    }

    public function sinNotificar(): static
    {
        return $this->state(fn () => ['notificado_at' => null]);
    }
}
