<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->enum('tipo', ['Levantamiento de requisito', 'Convalidación']);
            $table->unsignedBigInteger('curso_id')
                ->comment('Curso a matricular / curso interno al que aspira');
            $table->unsignedBigInteger('curso_requisito_id')->nullable()
                ->comment('Requisito no cumplido');
            $table->string('institucion_origen', 150)->nullable()->comment('Solo convalidación');
            $table->string('curso_externo', 150)->nullable()->comment('Solo convalidación');
            $table->unsignedBigInteger('convalidacion_historica_id')->nullable()
                ->comment('Precedente encontrado');
            $table->enum('resultado_motor', [
                'Procede automáticamente',
                'No procede',
                'Requiere revisión manual',
            ])->nullable()->comment('Primer resultado concluyente del motor');
            $table->unsignedBigInteger('regla_incumplida_id')->nullable()
                ->comment('Regla que produjo No procede');
            $table->enum('estado', [
                'Pendiente de revisión',
                'En revisión',
                'Aprobada',
                'Denegada',
            ])->default('Pendiente de revisión');
            $table->date('fecha_estimada_resolucion')->nullable()
                ->comment('Si no se ingresa en 24h la app asigna 5 días hábiles');
            $table->unsignedBigInteger('revisor_id')->nullable()
                ->comment('Usuario revisor (Docencia/Comisión)');
            $table->timestamps();

            $table->index(['tipo', 'estado', 'created_at'], 'solicitudes_bandeja_index');
            $table->index(['estudiante_id', 'estado'], 'solicitudes_estudiante_index');
            $table->index('curso_id', 'solicitudes_curso_id_index');
            $table->index('curso_requisito_id', 'solicitudes_curso_requisito_index');
            $table->index('convalidacion_historica_id', 'solicitudes_convalidacion_hist_index');
            $table->index('regla_incumplida_id', 'solicitudes_regla_incumplida_index');
            $table->index('revisor_id', 'solicitudes_revisor_id_index');

            $table->foreign('estudiante_id', 'fk_solicitudes_estudiante_id')
                ->references('id')->on('estudiantes')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('curso_id', 'fk_solicitudes_curso_id')
                ->references('id')->on('cursos')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('curso_requisito_id', 'fk_solicitudes_curso_requisito_id')
                ->references('id')->on('cursos')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('convalidacion_historica_id', 'fk_solicitudes_convalidacion_historica_id')
                ->references('id')->on('convalidaciones_historicas')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('regla_incumplida_id', 'fk_solicitudes_regla_incumplida_id')
                ->references('id')->on('reglas_levantamiento')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('revisor_id', 'fk_solicitudes_revisor_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
