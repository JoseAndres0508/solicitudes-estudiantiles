<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('solicitud_estados_historial')]
#[Fillable(['solicitud_id', 'estado_anterior', 'estado_nuevo', 'comentario', 'user_id', 'notificado_at'])]
class SolicitudEstadoHistorial extends Model
{
    use HasFactory;

    /** La tabla solo tiene created_at. */
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['notificado_at' => 'datetime'];
    }

    /** @return BelongsTo<Solicitud, $this> */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
