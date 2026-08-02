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
        Schema::create('levantamientos_otorgados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('requisito_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes')->nullOnDelete()->cascadeOnUpdate();
            $table->date('fecha_otorgamiento');
            $table->timestamps();

            $table->unique(['estudiante_id', 'curso_id', 'requisito_id'], 'levantamientos_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levantamientos_otorgados');
    }
};
