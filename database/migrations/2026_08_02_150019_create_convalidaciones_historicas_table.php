<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convalidaciones_historicas', function (Blueprint $table) {
            $table->id();
            $table->string('institucion', 150);
            $table->string('curso_externo', 150);
            $table->unsignedBigInteger('curso_id')->comment('Curso interno UTN equivalente');
            $table->enum('resultado', ['Aprobada', 'Denegada']);
            $table->string('numero_resolucion', 60)
                ->comment('Resolución de referencia del precedente');
            $table->timestamps();

            $table->index(['institucion', 'curso_externo'], 'convalidaciones_historicas_busqueda_index');
            $table->index('curso_id', 'convalidaciones_historicas_curso_id_index');

            $table->foreign('curso_id', 'fk_convalidaciones_historicas_curso_id')
                ->references('id')->on('cursos')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convalidaciones_historicas');
    }
};
