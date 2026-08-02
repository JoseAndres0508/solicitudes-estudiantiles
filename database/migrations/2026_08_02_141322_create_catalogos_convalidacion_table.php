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
        Schema::create('catalogos_convalidacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('institucion', 180);
            $table->string('curso_externo', 180);
            $table->string('institucion_normalizada', 180)->comment('Minúsculas, sin tildes: permite localizar precedentes pese a diferencias de escritura');
            $table->string('curso_externo_normalizado', 180);
            $table->enum('resultado', ['Aprobada', 'Denegada']);
            $table->string('resolucion_referencia', 120)->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->timestamps();

            $table->index(['institucion_normalizada', 'curso_externo_normalizado'], 'catalogos_conv_busqueda_index');
            $table->index('curso_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogos_convalidacion');
    }
};
