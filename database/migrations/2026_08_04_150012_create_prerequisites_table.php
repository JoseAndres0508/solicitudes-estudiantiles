<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prerequisites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_plan_id');
            $table->unsignedBigInteger('required_course_id')
                ->comment('Curso que debe aprobarse primero');
            $table->unsignedBigInteger('target_course_id')
                ->comment('Curso que exige el requisito');
            $table->timestamp('created_at')->nullable();

            $table->unique(
                ['study_plan_id', 'required_course_id', 'target_course_id'],
                'prerequisites_plan_pair_unique'
            );
            $table->index('target_course_id', 'prerequisites_target_course_index');
            $table->index('required_course_id', 'prerequisites_required_course_index');

            $table->foreign('study_plan_id', 'fk_prerequisites_study_plan_id')
                ->references('id')->on('study_plans')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('required_course_id', 'fk_prerequisites_required_course_id')
                ->references('id')->on('courses')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('target_course_id', 'fk_prerequisites_target_course_id')
                ->references('id')->on('courses')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE prerequisites ADD CONSTRAINT chk_prerequisites_distinct
            CHECK (required_course_id <> target_course_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('prerequisites');
    }
};
