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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('carrera_id')->constrained('carreras')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('cedula', 12)->unique();
            $table->string('nombre', 150);
            $table->string('correo', 150)->nullable();
            $table->boolean('plan_terminal')->default(false)->comment('Habilita la regla de plan terminal (ES-01)');
            $table->timestamps();

            $table->index('carrera_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
