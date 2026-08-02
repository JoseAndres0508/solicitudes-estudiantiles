<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('plan_estudio_id');
            $table->unsignedTinyInteger('nivel_actual')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->primary(['estudiante_id', 'plan_estudio_id']);
            $table->index(['plan_estudio_id', 'nivel_actual'], 'estudiante_plan_plan_nivel_index');

            $table->foreign('estudiante_id', 'fk_estudiante_plan_estudiante_id')
                ->references('id')->on('estudiantes')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('plan_estudio_id', 'fk_estudiante_plan_plan_estudio_id')
                ->references('id')->on('planes_estudio')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante_plan');
    }
};
