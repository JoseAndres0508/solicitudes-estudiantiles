<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('type', ['Levantamiento de requisito', 'Convalidación']);
            $table->unsignedBigInteger('course_id')
                ->comment('Curso a matricular / curso interno al que aspira');
            $table->unsignedBigInteger('prerequisite_course_id')->nullable()
                ->comment('Requisito no cumplido');
            $table->string('source_institution', 150)->nullable()
                ->comment('Solo en solicitudes de Convalidación');
            $table->string('external_course', 150)->nullable()
                ->comment('Solo en solicitudes de Convalidación');
            $table->unsignedBigInteger('transfer_credit_precedent_id')->nullable()
                ->comment('Precedente encontrado, si existe');
            $table->enum('engine_result', [
                'Procede automáticamente',
                'No procede',
                'Requiere revisión manual',
            ])->nullable()->comment('Primer resultado concluyente del motor de reglas');
            $table->unsignedBigInteger('failed_rule_id')->nullable()
                ->comment('Regla que produjo el resultado No procede');
            $table->enum('status', [
                'Pendiente de revisión',
                'En revisión',
                'Aprobada',
                'Denegada',
            ])->default('Pendiente de revisión');
            $table->date('estimated_resolution_date')->nullable()
                ->comment('Si no se ingresa en 24h la app asigna 5 días hábiles');
            $table->unsignedBigInteger('reviewer_id')->nullable()
                ->comment('Usuario revisor (Docencia/Comisión)');
            $table->timestamps();

            $table->index(['type', 'status', 'created_at'], 'student_requests_inbox_index');
            $table->index(['student_id', 'status'], 'student_requests_student_index');
            $table->index('course_id', 'student_requests_course_id_index');
            $table->index('prerequisite_course_id', 'student_requests_prerequisite_course_index');
            $table->index('transfer_credit_precedent_id', 'student_requests_precedent_index');
            $table->index('failed_rule_id', 'student_requests_failed_rule_index');
            $table->index('reviewer_id', 'student_requests_reviewer_id_index');

            $table->foreign('student_id', 'fk_student_requests_student_id')
                ->references('id')->on('students')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_student_requests_course_id')
                ->references('id')->on('courses')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('prerequisite_course_id', 'fk_student_requests_prerequisite_course_id')
                ->references('id')->on('courses')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('transfer_credit_precedent_id', 'fk_student_requests_precedent_id')
                ->references('id')->on('transfer_credit_precedents')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('failed_rule_id', 'fk_student_requests_failed_rule_id')
                ->references('id')->on('waiver_rules')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('reviewer_id', 'fk_student_requests_reviewer_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        // Cada tipo de solicitud lleva sus propios campos obligatorios.
        DB::statement("ALTER TABLE student_requests ADD CONSTRAINT chk_student_requests_transfer_fields
            CHECK (type <> 'Convalidación'
                OR (source_institution IS NOT NULL AND external_course IS NOT NULL))");

        DB::statement("ALTER TABLE student_requests ADD CONSTRAINT chk_student_requests_waiver_fields
            CHECK (type <> 'Levantamiento de requisito' OR prerequisite_course_id IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('student_requests');
    }
};
