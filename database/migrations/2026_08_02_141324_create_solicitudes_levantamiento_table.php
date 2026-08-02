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
        Schema::create('solicitudes_levantamiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->unique()->constrained('solicitudes')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('curso_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('requisito_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('resultado_motor', ['Procede', 'No procede', 'Revisión manual', 'Sin reglas'])->nullable();
            $table->text('justificacion_motor')->nullable();
            $table->timestamps();

            $table->index(['curso_id', 'requisito_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_levantamiento');
    }
};
