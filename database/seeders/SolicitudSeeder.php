<?php

namespace Database\Seeders;

use App\Models\ConvalidacionHistorica;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\ReglaLevantamiento;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\SolicitudEstadoHistorial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Solicitudes de ambos tipos y en varios estados, para poblar la bandeja de ES-04. */
class SolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $revisor = $this->revisor('docencia@utn.ac.cr', 'Bandeja de Docencia', 'Coordinadora de Docencia');
        $comision = $this->revisor('comision@utn.ac.cr', 'Comisión Técnica', 'Comisión Técnica');

        $curso = fn (string $c) => Curso::firstWhere('codigo', $c);
        $est = fn (string $ced) => Estudiante::firstWhere('cedula', $ced);

        // ES-01 — resuelta a favor por la regla de nota mínima.
        $ana = Solicitud::create([
            'estudiante_id' => $est('2-0801-0455')->id,
            'tipo' => Solicitud::TIPO_LEVANTAMIENTO,
            'curso_id' => $curso('ITI-401')->id,
            'curso_requisito_id' => $curso('ITI-302')->id,
            'resultado_motor' => Solicitud::MOTOR_PROCEDE,
            'estado' => Solicitud::ESTADO_APROBADA,
            'revisor_id' => $revisor->id,
            'fecha_estimada_resolucion' => now()->subDays(2)->toDateString(),
        ]);
        $this->traza($ana, null, Solicitud::ESTADO_PENDIENTE, $revisor, null);
        $this->traza($ana, Solicitud::ESTADO_PENDIENTE, Solicitud::ESTADO_APROBADA, $revisor,
            'El motor confirmó nota 92 en ITI-302, sobre el mínimo de 85.');

        // ES-01 — denegada: ninguna regla concluye a favor.
        $regla = ReglaLevantamiento::where('curso_id', $curso('ITI-401')->id)->where('orden', 1)->first();
        $bruno = Solicitud::create([
            'estudiante_id' => $est('1-1702-0388')->id,
            'tipo' => Solicitud::TIPO_LEVANTAMIENTO,
            'curso_id' => $curso('ITI-401')->id,
            'curso_requisito_id' => $curso('ITI-302')->id,
            'resultado_motor' => Solicitud::MOTOR_NO_PROCEDE,
            'regla_incumplida_id' => $regla?->id,
            'estado' => Solicitud::ESTADO_DENEGADA,
            'revisor_id' => $revisor->id,
        ]);
        $this->traza($bruno, null, Solicitud::ESTADO_PENDIENTE, $revisor, null);
        $this->traza($bruno, Solicitud::ESTADO_PENDIENTE, Solicitud::ESTADO_DENEGADA, $revisor,
            'Nota 71 en ITI-302, por debajo del mínimo de 85; tampoco alcanza los 60 créditos.');

        // ES-01 — curso sin reglas configuradas: se eleva a revisión manual.
        $manual = Solicitud::create([
            'estudiante_id' => $est('2-0655-0912')->id,
            'tipo' => Solicitud::TIPO_LEVANTAMIENTO,
            'curso_id' => $curso('ITI-501')->id,
            'curso_requisito_id' => $curso('ITI-402')->id,
            'resultado_motor' => Solicitud::MOTOR_REVISION_MANUAL,
            'estado' => Solicitud::ESTADO_PENDIENTE,
        ]);
        $this->traza($manual, null, Solicitud::ESTADO_PENDIENTE, null, null);

        // ES-02 — con precedente aprobado en el catálogo.
        $precedente = ConvalidacionHistorica::firstWhere('curso_externo', 'PROGRAMACIÓN I');
        $conv = Solicitud::create([
            'estudiante_id' => $est('2-0655-0912')->id,
            'tipo' => Solicitud::TIPO_CONVALIDACION,
            'curso_id' => $curso('ITI-101')->id,
            'institucion_origen' => 'Universidad de Costa Rica',
            'curso_externo' => 'PROGRAMACIÓN I',
            'convalidacion_historica_id' => $precedente?->id,
            'estado' => Solicitud::ESTADO_EN_REVISION,
            'revisor_id' => $comision->id,
            'fecha_estimada_resolucion' => now()->addWeekdays(5)->toDateString(),
        ]);
        $this->traza($conv, null, Solicitud::ESTADO_PENDIENTE, null, null);
        $this->traza($conv, Solicitud::ESTADO_PENDIENTE, Solicitud::ESTADO_EN_REVISION, $comision,
            'Precedente aprobado encontrado: resolución CTC-114-2024.');

        // ES-02 — sin precedente en el catálogo.
        $sinPrec = Solicitud::create([
            'estudiante_id' => $est('1-1702-0388')->id,
            'tipo' => Solicitud::TIPO_CONVALIDACION,
            'curso_id' => $curso('ITI-502')->id,
            'institucion_origen' => 'Universidad Latina',
            'curso_externo' => 'INGENIERÍA DE SISTEMAS',
            'estado' => Solicitud::ESTADO_PENDIENTE,
        ]);
        $this->traza($sinPrec, null, Solicitud::ESTADO_PENDIENTE, null, null);

        // ES-03 — recibida hace más de 24 h sin fecha estimada: la debe asignar el sistema.
        $vencida = Solicitud::create([
            'estudiante_id' => $est('3-0499-0201')->id,
            'tipo' => Solicitud::TIPO_LEVANTAMIENTO,
            'curso_id' => $curso('ITI-402')->id,
            'curso_requisito_id' => $curso('ITI-301')->id,
            'resultado_motor' => Solicitud::MOTOR_REVISION_MANUAL,
            'estado' => Solicitud::ESTADO_EN_REVISION,
            'revisor_id' => $revisor->id,
        ]);
        $vencida->forceFill(['created_at' => now()->subHours(30)])->save();
        $this->traza($vencida, null, Solicitud::ESTADO_PENDIENTE, null, null);
    }

    private function revisor(string $email, string $nombre, string $rol): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $nombre, 'password' => Hash::make('password'), 'email_verified_at' => now()],
        );
        $user->roles()->syncWithPivotValues([Rol::firstWhere('name', $rol)->id], ['created_at' => now()]);

        return $user;
    }

    private function traza(Solicitud $s, ?string $de, string $a, ?User $user, ?string $comentario): void
    {
        SolicitudEstadoHistorial::create([
            'solicitud_id' => $s->id,
            'estado_anterior' => $de,
            'estado_nuevo' => $a,
            'comentario' => $comentario,
            'user_id' => $user?->id,
            'notificado_at' => now(),
        ]);
    }
}
