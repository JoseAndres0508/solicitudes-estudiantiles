<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Table('solicitudes')]
#[Fillable([
    'estudiante_id', 'tipo', 'curso_id', 'curso_requisito_id',
    'institucion_origen', 'curso_externo', 'convalidacion_historica_id',
    'resultado_motor', 'regla_incumplida_id', 'estado',
    'fecha_estimada_resolucion', 'revisor_id',
])]
class Solicitud extends Model
{
    use HasFactory;

    public const TIPO_LEVANTAMIENTO = 'Levantamiento de requisito';
    public const TIPO_CONVALIDACION = 'Convalidación';

    public const ESTADO_PENDIENTE = 'Pendiente de revisión';
    public const ESTADO_EN_REVISION = 'En revisión';
    public const ESTADO_APROBADA = 'Aprobada';
    public const ESTADO_DENEGADA = 'Denegada';

    /** Los tres resultados que ES-01 exige devolver de inmediato. */
    public const MOTOR_PROCEDE = 'Procede automáticamente';
    public const MOTOR_NO_PROCEDE = 'No procede';
    public const MOTOR_REVISION_MANUAL = 'Requiere revisión manual';

    protected function casts(): array
    {
        return ['fecha_estimada_resolucion' => 'date'];
    }

    /** Bandeja centralizada de ES-04: usa solicitudes_bandeja_index. */
    /** @param Builder<Solicitud> $query */
    public function scopeBandeja(Builder $query): void
    {
        $query->orderByDesc('created_at');
    }

    /** @param Builder<Solicitud> $query */
    public function scopeDeTipo(Builder $query, string $tipo): void
    {
        $query->where('tipo', $tipo);
    }

    /** @return BelongsTo<Estudiante, $this> */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /** Curso a matricular, o curso interno al que aspira. */
    /** @return BelongsTo<Curso, $this> */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /** Requisito no cumplido; solo en levantamientos. */
    /** @return BelongsTo<Curso, $this> */
    public function cursoRequisito(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_requisito_id');
    }

    /** @return BelongsTo<ConvalidacionHistorica, $this> */
    public function convalidacionHistorica(): BelongsTo
    {
        return $this->belongsTo(ConvalidacionHistorica::class);
    }

    /** @return BelongsTo<ReglaLevantamiento, $this> */
    public function reglaIncumplida(): BelongsTo
    {
        return $this->belongsTo(ReglaLevantamiento::class, 'regla_incumplida_id');
    }

    /** @return BelongsTo<User, $this> */
    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }

    /** @return HasMany<SolicitudEstadoHistorial, $this> */
    public function estados(): HasMany
    {
        return $this->hasMany(SolicitudEstadoHistorial::class)->orderBy('created_at');
    }

    /** @return MorphMany<Archivo, $this> */
    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'archivable');
    }
}
