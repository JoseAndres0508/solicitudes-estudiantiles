<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('term_number')->comment('1, 2 o 3');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique(['year', 'term_number'], 'academic_terms_year_term_number_unique');
        });

        DB::statement('ALTER TABLE academic_terms ADD CONSTRAINT chk_academic_terms_term_number
            CHECK (term_number BETWEEN 1 AND 3)');

        DB::statement('ALTER TABLE academic_terms ADD CONSTRAINT chk_academic_terms_date_range
            CHECK (end_date > start_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
