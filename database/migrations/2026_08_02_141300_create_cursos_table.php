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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('codigo', 30)->unique()->comment('Ej.: ITI-224, ITIEL-13');
            $table->string('nombre', 150);
            $table->unsignedTinyInteger('creditos')->default(0)->comment('Ampliación del estándar: requerido por regla de créditos acumulados (ES-01)');
            $table->boolean('requiere_laboratorio')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

    $table->index('carrera_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
