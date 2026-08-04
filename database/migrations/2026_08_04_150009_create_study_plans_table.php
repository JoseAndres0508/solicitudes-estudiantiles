<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('degree_program_id');
            $table->string('name', 120)->comment('Ej.: Plan 2023, Plan 2025');
            $table->year('implementation_year');
            $table->enum('classification', ['Vigente', 'Terminal'])->default('Vigente');
            $table->date('enrollment_closing_date')->nullable()
                ->comment('Obligatoria solo para planes Terminal');
            $table->timestamps();

            $table->unique(['degree_program_id', 'name'], 'study_plans_program_name_unique');
            $table->index('classification', 'study_plans_classification_index');

            $table->foreign('degree_program_id', 'fk_study_plans_degree_program_id')
                ->references('id')->on('degree_programs')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement("ALTER TABLE study_plans ADD CONSTRAINT chk_study_plans_terminal_date
            CHECK (classification = 'Vigente' OR enrollment_closing_date IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('study_plans');
    }
};
