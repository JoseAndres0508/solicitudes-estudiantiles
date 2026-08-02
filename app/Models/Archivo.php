<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'uuid', 'user_id', 'archivable_type', 'archivable_id', 'tipo_documento',
    'nombre_original', 'disco', 'ruta', 'mime_type', 'tamano_bytes', 'hash_sha256',
])]
class Archivo extends Model
{
    use HasFactory;

    /** Límite documental de ES-01 y ES-02: 5 MB. */
    public const TAMANO_MAXIMO_BYTES = 5_242_880;

    /** Tipos de documento que exige ES-02. */
    public const DOCUMENTOS_CONVALIDACION = [
        'Programa del curso externo',
        'Certificación de calificación',
        'Constancia de la institución',
    ];

    protected function casts(): array
    {
        return ['tamano_bytes' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return MorphTo<Model, $this> */
    public function archivable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
