<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expedientes_academicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('periodo_academico_id')->nullable()->constrained('periodos_academicos')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('nota', 5, 2)->comment('Escala 0-100');
            $table->boolean('aprobado');
            $table->timestamps();

            $table->unique(['estudiante_id', 'curso_id', 'periodo_academico_id'], 'expedientes_estudiante_curso_periodo_unique');
            $table->index(['estudiante_id', 'aprobado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes_academicos');
    }
};
