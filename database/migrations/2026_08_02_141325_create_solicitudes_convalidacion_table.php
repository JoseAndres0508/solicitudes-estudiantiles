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
        Schema::create('solicitudes_convalidacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->unique()->constrained('solicitudes')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate()->comment('Curso interno al que aspira');
            $table->string('institucion', 180);
            $table->string('curso_externo', 180);
            $table->string('institucion_normalizada', 180);
            $table->string('curso_externo_normalizado', 180);
            $table->foreignId('catalogo_convalidacion_id')->nullable()->constrained('catalogos_convalidacion')->nullOnDelete()->cascadeOnUpdate()->comment('Precedente localizado; NULL si no existe');
            $table->timestamps();

            $table->index(['institucion_normalizada', 'curso_externo_normalizado'], 'solicitudes_conv_busqueda_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_convalidacion');
    }
};
