<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_level', function (Blueprint $table) {
            $table->unsignedBigInteger('level_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedTinyInteger('credits')
                ->comment('Créditos del curso EN ESTE PLAN: base de la regla (b) de ES-01');
            $table->timestamp('created_at')->nullable();

            $table->primary(['level_id', 'course_id']);
            $table->index('course_id', 'course_level_course_id_index');

            $table->foreign('level_id', 'fk_course_level_level_id')
                ->references('id')->on('levels')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_course_level_course_id')
                ->references('id')->on('courses')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE course_level ADD CONSTRAINT chk_course_level_credits
            CHECK (credits > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('course_level');
    }
};
