<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Attachment> */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->slug(3).'.pdf';

        return [
            'uuid' => fake()->unique()->uuid(),
            'user_id' => User::factory(),
            'attachable_type' => StudentRequest::class,
            'attachable_id' => StudentRequest::factory(),
            'document_type' => 'Programa del curso externo',
            'original_name' => $name,
            'disk' => 'local',
            'path' => 'requests/'.Str::random(8).'/'.$name,
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(50_000, Attachment::MAX_SIZE_BYTES),
            'sha256_hash' => hash('sha256', Str::random(32)),
        ];
    }

    public function ofType(string $documentType): static
    {
        return $this->state(fn () => ['document_type' => $documentType]);
    }

    public function image(): static
    {
        return $this->state(fn () => [
            'mime_type' => 'image/png',
            'original_name' => fake()->slug(2).'.png',
        ]);
    }
}
