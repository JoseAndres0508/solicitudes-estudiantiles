<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiver_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedTinyInteger('evaluation_order')->comment('Orden de evaluación del motor');
            $table->enum('type', [
                'Requisito aprobado con nota mínima',
                'Créditos o cursos acumulados',
                'Pertenencia a plan terminal',
                'Siempre revisión manual',
            ]);
            $table->unsignedBigInteger('prerequisite_course_id')->nullable()
                ->comment('Parámetro del tipo (a)');
            $table->decimal('minimum_grade', 5, 2)->nullable()->comment('Parámetro N del tipo (a)');
            $table->unsignedSmallInteger('minimum_accumulated')->nullable()
                ->comment('Parámetro K del tipo (b)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['course_id', 'evaluation_order'], 'waiver_rules_course_order_unique');
            $table->index('prerequisite_course_id', 'waiver_rules_prerequisite_course_index');

            $table->foreign('course_id', 'fk_waiver_rules_course_id')
                ->references('id')->on('courses')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('prerequisite_course_id', 'fk_waiver_rules_prerequisite_course_id')
                ->references('id')->on('courses')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE waiver_rules ADD CONSTRAINT chk_waiver_rules_distinct_course
            CHECK (prerequisite_course_id IS NULL OR prerequisite_course_id <> course_id)');

        // Cada tipo de regla lleva exactamente los parámetros que necesita.
        DB::statement("ALTER TABLE waiver_rules ADD CONSTRAINT chk_waiver_rules_parameters
            CHECK (
              (type = 'Requisito aprobado con nota mínima'
                AND prerequisite_course_id IS NOT NULL AND minimum_grade IS NOT NULL
                AND minimum_accumulated IS NULL)
              OR (type = 'Créditos o cursos acumulados'
                AND minimum_accumulated IS NOT NULL
                AND prerequisite_course_id IS NULL AND minimum_grade IS NULL)
              OR (type IN ('Pertenencia a plan terminal','Siempre revisión manual')
                AND prerequisite_course_id IS NULL AND minimum_grade IS NULL
                AND minimum_accumulated IS NULL)
            )");
    }

    public function down(): void
    {
        Schema::dropIfExists('waiver_rules');
    }
};
