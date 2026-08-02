<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_nivel', function (Blueprint $table) {
            $table->unsignedBigInteger('nivel_id');
            $table->unsignedBigInteger('curso_id');
            $table->unsignedTinyInteger('creditos')
                ->comment('Créditos del curso EN ESTE PLAN: base de la regla (b) de ES-01');
            $table->timestamp('created_at')->nullable();

            $table->primary(['nivel_id', 'curso_id']);
            $table->index('curso_id', 'curso_nivel_curso_id_index');

            $table->foreign('nivel_id', 'fk_curso_nivel_nivel_id')
                ->references('id')->on('niveles')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('curso_id', 'fk_curso_nivel_curso_id')
                ->references('id')->on('cursos')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_nivel');
    }
};
