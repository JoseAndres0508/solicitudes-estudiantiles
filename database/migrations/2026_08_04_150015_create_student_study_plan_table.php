<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_study_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('study_plan_id');
            $table->unsignedTinyInteger('current_level')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->primary(['student_id', 'study_plan_id']);
            $table->index(['study_plan_id', 'current_level'], 'student_study_plan_plan_level_index');

            $table->foreign('student_id', 'fk_student_study_plan_student_id')
                ->references('id')->on('students')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('study_plan_id', 'fk_student_study_plan_study_plan_id')
                ->references('id')->on('study_plans')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_study_plan');
    }
};
