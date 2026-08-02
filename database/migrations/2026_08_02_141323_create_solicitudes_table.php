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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('tipo', ['Levantamiento', 'Convalidación']);
            $table->enum('estado', ['Pendiente', 'En revisión', 'Aprobada', 'Denegada'])->default('Pendiente');
            $table->foreignId('revisor_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('fecha_recepcion')->useCurrent();
            $table->date('fecha_estimada_resolucion')->nullable();
            $table->boolean('fecha_asignada_automaticamente')->default(false)->comment('True si venció el plazo de 24h sin que el revisor indicara fecha (ES-03)');
            $table->timestamp('fecha_resolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estado', 'tipo']);
            $table->index(['estudiante_id', 'estado']);
            $table->index('fecha_recepcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
