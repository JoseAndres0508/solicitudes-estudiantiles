<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_estudio_id');
            $table->unsignedTinyInteger('numero');
            $table->timestamps();

            $table->unique(['plan_estudio_id', 'numero'], 'niveles_plan_numero_unique');

            $table->foreign('plan_estudio_id', 'fk_niveles_plan_estudio_id')
                ->references('id')->on('planes_estudio')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};
