<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_plan_id');
            $table->unsignedTinyInteger('number');
            $table->timestamps();

            $table->unique(['study_plan_id', 'number'], 'levels_plan_number_unique');

            $table->foreign('study_plan_id', 'fk_levels_study_plan_id')
                ->references('id')->on('study_plans')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE levels ADD CONSTRAINT chk_levels_number CHECK (number > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
