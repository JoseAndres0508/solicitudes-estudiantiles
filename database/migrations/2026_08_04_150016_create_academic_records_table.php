<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('academic_term_id')->nullable();
            $table->enum('status', [
                'Aprobado',
                'Reprobado',
                'Acreditado por equiparación',
                'Acreditado por convalidación',
                'Requisito levantado',
            ]);
            $table->decimal('grade', 5, 2)->nullable();
            $table->unsignedBigInteger('course_equivalence_id')->nullable()
                ->comment('Resolución de referencia de la acreditación');
            $table->timestamps();

            $table->index(['student_id', 'course_id'], 'academic_records_student_course_index');
            $table->index(['course_id', 'status'], 'academic_records_course_status_index');
            $table->index('academic_term_id', 'academic_records_term_index');
            $table->index('course_equivalence_id', 'academic_records_equivalence_index');

            $table->foreign('student_id', 'fk_academic_records_student_id')
                ->references('id')->on('students')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_academic_records_course_id')
                ->references('id')->on('courses')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('academic_term_id', 'fk_academic_records_academic_term_id')
                ->references('id')->on('academic_terms')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('course_equivalence_id', 'fk_academic_records_course_equivalence_id')
                ->references('id')->on('course_equivalences')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE academic_records ADD CONSTRAINT chk_academic_records_grade
            CHECK (grade IS NULL OR (grade >= 0 AND grade <= 100))');
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_records');
    }
};
