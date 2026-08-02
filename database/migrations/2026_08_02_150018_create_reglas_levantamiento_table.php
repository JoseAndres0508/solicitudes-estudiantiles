<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglas_levantamiento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_id');
            $table->unsignedTinyInteger('orden')->comment('Orden de evaluación del motor');
            $table->enum('tipo', [
                'Requisito aprobado con nota mínima',
                'Créditos o cursos acumulados',
                'Pertenencia a plan terminal',
                'Siempre revisión manual',
            ]);
            $table->unsignedBigInteger('curso_requisito_id')->nullable()
                ->comment('Parámetro del tipo (a)');
            $table->decimal('nota_minima', 5, 2)->nullable()->comment('Parámetro N del tipo (a)');
            $table->unsignedSmallInteger('minimo_acumulado')->nullable()
                ->comment('Parámetro K del tipo (b)');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['curso_id', 'orden'], 'reglas_levantamiento_curso_orden_unique');
            $table->index('curso_requisito_id', 'reglas_levantamiento_curso_requisito_index');

            $table->foreign('curso_id', 'fk_reglas_levantamiento_curso_id')
                ->references('id')->on('cursos')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('curso_requisito_id', 'fk_reglas_levantamiento_curso_requisito_id')
                ->references('id')->on('cursos')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglas_levantamiento');
    }
};
