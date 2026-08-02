<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_estudio_id');
            $table->unsignedBigInteger('curso_requerido_id')
                ->comment('Curso que debe aprobarse primero');
            $table->unsignedBigInteger('curso_exige_id')
                ->comment('Curso que exige el requisito');
            $table->timestamp('created_at')->nullable();

            $table->unique(
                ['plan_estudio_id', 'curso_requerido_id', 'curso_exige_id'],
                'requisitos_plan_par_unique'
            );
            $table->index('curso_exige_id', 'requisitos_curso_exige_index');
            $table->index('curso_requerido_id', 'requisitos_curso_requerido_index');

            $table->foreign('plan_estudio_id', 'fk_requisitos_plan_estudio_id')
                ->references('id')->on('planes_estudio')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('curso_requerido_id', 'fk_requisitos_curso_requerido_id')
                ->references('id')->on('cursos')
                ->cascadeOnDelete()->restrictOnUpdate();
            $table->foreign('curso_exige_id', 'fk_requisitos_curso_exige_id')
                ->references('id')->on('cursos')
                ->cascadeOnDelete()->restrictOnUpdate();
        });

        DB::statement('ALTER TABLE requisitos ADD CONSTRAINT chk_requisitos_distintos
            CHECK (curso_requerido_id <> curso_exige_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos');
    }
};
