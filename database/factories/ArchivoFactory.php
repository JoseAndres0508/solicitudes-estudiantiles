<?php

namespace Database\Factories;

use App\Models\Archivo;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Archivo> */
class ArchivoFactory extends Factory
{
    protected $model = Archivo::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $nombre = fake()->slug(3).'.pdf';

        return [
            'uuid' => fake()->unique()->uuid(),
            'user_id' => User::factory(),
            'archivable_type' => Solicitud::class,
            'archivable_id' => Solicitud::factory(),
            'tipo_documento' => 'Programa del curso externo',
            'nombre_original' => $nombre,
            'disco' => 'local',
            'ruta' => 'solicitudes/'.Str::random(8).'/'.$nombre,
            'mime_type' => 'application/pdf',
            'tamano_bytes' => fake()->numberBetween(50_000, Archivo::TAMANO_MAXIMO_BYTES),
            'hash_sha256' => hash('sha256', Str::random(32)),
        ];
    }

    public function deTipo(string $tipoDocumento): static
    {
        return $this->state(fn () => ['tipo_documento' => $tipoDocumento]);
    }

    public function imagen(): static
    {
        return $this->state(fn () => [
            'mime_type' => 'image/png',
            'nombre_original' => fake()->slug(2).'.png',
        ]);
    }
}
