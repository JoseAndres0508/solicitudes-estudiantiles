<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_estudio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carrera_id');
            $table->string('nombre', 120)->comment('Ej.: Plan 2023, Plan 2025');
            $table->year('anio_implementacion');
            $table->enum('clasificacion', ['Vigente', 'Terminal'])->default('Vigente');
            $table->date('fecha_cierre_matricula')->nullable()
                ->comment('Obligatoria solo para planes Terminal');
            $table->timestamps();

            $table->unique(['carrera_id', 'nombre'], 'planes_estudio_carrera_nombre_unique');
            $table->index('clasificacion', 'planes_estudio_clasificacion_index');

            $table->foreign('carrera_id', 'fk_planes_estudio_carrera_id')
                ->references('id')->on('carreras')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement("ALTER TABLE planes_estudio ADD CONSTRAINT chk_planes_terminal_fecha
            CHECK (clasificacion = 'Vigente' OR fecha_cierre_matricula IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_estudio');
    }
};
