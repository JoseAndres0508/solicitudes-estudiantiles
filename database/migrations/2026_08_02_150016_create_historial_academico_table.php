<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_academico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('curso_id');
            $table->unsignedBigInteger('periodo_academico_id')->nullable();
            $table->enum('estado', [
                'Aprobado',
                'Reprobado',
                'Acreditado por equiparación',
                'Acreditado por convalidación',
                'Requisito levantado',
            ]);
            $table->decimal('nota', 5, 2)->nullable();
            $table->unsignedBigInteger('equiparacion_id')->nullable()
                ->comment('Resolución de referencia de la acreditación');
            $table->timestamps();

            $table->index(['estudiante_id', 'curso_id'], 'historial_academico_estudiante_curso_index');
            $table->index(['curso_id', 'estado'], 'historial_academico_curso_estado_index');
            $table->index('periodo_academico_id', 'historial_academico_periodo_index');
            $table->index('equiparacion_id', 'historial_academico_equiparacion_index');

            $table->foreign('estudiante_id', 'fk_historial_academico_estudiante_id')
                ->references('id')->on('estudiantes')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('curso_id', 'fk_historial_academico_curso_id')
                ->references('id')->on('cursos')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('periodo_academico_id', 'fk_historial_academico_periodo_id')
                ->references('id')->on('periodos_academicos')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('equiparacion_id', 'fk_historial_academico_equiparacion_id')
                ->references('id')->on('equiparaciones')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_academico');
    }
};
