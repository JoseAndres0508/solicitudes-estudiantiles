<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('degree_program_id')->nullable()
                ->comment('NULL cuando es un curso de servicio transversal');
            $table->string('code', 30)->comment('Ej.: ITI-224, ITIEL-13');
            $table->string('name', 150);
            $table->boolean('is_service_course')->default(false)
                ->comment('1 = curso transversal administrado por Docencia');
            $table->boolean('is_bottleneck')->default(false)
                ->comment('1 = curso pinned: prioridad de horario y aula');
            $table->boolean('requires_lab')->default(false);
            $table->enum('lab_type', [
                'Laboratorio de cómputo',
                'Laboratorio de ciencias',
                'Laboratorio de idiomas',
            ])->nullable()->comment('Tipo de laboratorio requerido');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code', 'courses_code_unique');
            $table->index('degree_program_id', 'courses_degree_program_id_index');

            $table->foreign('degree_program_id', 'fk_courses_degree_program_id')
                ->references('id')->on('degree_programs')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE courses ADD CONSTRAINT chk_courses_service_program
            CHECK (is_service_course = 1 OR degree_program_id IS NOT NULL)');

        DB::statement('ALTER TABLE courses ADD CONSTRAINT chk_courses_lab_type
            CHECK ((requires_lab = 1 AND lab_type IS NOT NULL)
                OR (requires_lab = 0 AND lab_type IS NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
