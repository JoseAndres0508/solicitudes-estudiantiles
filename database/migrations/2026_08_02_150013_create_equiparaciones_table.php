<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equiparaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curso_origen_id')->comment('Curso del plan anterior');
            $table->unsignedBigInteger('curso_destino_id')->comment('Curso equivalente del plan nuevo');
            $table->enum('sentido', ['Anterior a nuevo', 'Nuevo a anterior', 'Bidireccional']);
            $table->string('numero_resolucion', 60);
            $table->enum('estado', ['Vigente', 'Sustituida'])->default('Vigente');
            $table->unsignedBigInteger('sustituida_por_id')->nullable()
                ->comment('Equiparación que prevalece (RC-02)');
            $table->timestamps();

            $table->unique(
                ['curso_origen_id', 'curso_destino_id', 'numero_resolucion'],
                'equiparaciones_par_resolucion_unique'
            );
            $table->index('curso_destino_id', 'equiparaciones_curso_destino_index');
            $table->index('estado', 'equiparaciones_estado_index');
            $table->index('sustituida_por_id', 'equiparaciones_sustituida_por_index');

            $table->foreign('curso_origen_id', 'fk_equiparaciones_curso_origen_id')
                ->references('id')->on('cursos')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('curso_destino_id', 'fk_equiparaciones_curso_destino_id')
                ->references('id')->on('cursos')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('sustituida_por_id', 'fk_equiparaciones_sustituida_por_id')
                ->references('id')->on('equiparaciones')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE equiparaciones ADD CONSTRAINT chk_equiparaciones_distintos
            CHECK (curso_origen_id <> curso_destino_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('equiparaciones');
    }
};
