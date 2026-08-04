<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'uuid', 'user_id', 'attachable_type', 'attachable_id', 'document_type',
    'original_name', 'disk', 'path', 'mime_type', 'size_bytes', 'sha256_hash',
])]
class Attachment extends Model
{
    use HasFactory;

    /** Document size limit set by ES-01 and ES-02: 5 MB. */
    public const MAX_SIZE_BYTES = 5_242_880;

    /** The three documents ES-02 requires for a transfer credit request. */
    public const TRANSFER_CREDIT_DOCUMENTS = [
        'Programa del curso externo',
        'Certificación de calificación',
        'Constancia de la institución',
    ];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return MorphTo<Model, $this> */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
