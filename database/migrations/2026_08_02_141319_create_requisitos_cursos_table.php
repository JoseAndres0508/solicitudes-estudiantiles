<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requisitos_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('requisito_id')->constrained('cursos')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['curso_id', 'requisito_id']);
            $table->index('requisito_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitos_cursos');
    }
};
