<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()
                ->comment('Cuenta de acceso al portal');
            $table->string('cedula', 12);
            $table->string('nombre', 60);
            $table->string('primer_apellido', 60);
            $table->string('segundo_apellido', 60)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('cedula', 'estudiantes_cedula_unique');
            $table->unique('user_id', 'estudiantes_user_id_unique');
            $table->index(['primer_apellido', 'segundo_apellido'], 'estudiantes_apellidos_index');

            $table->foreign('user_id', 'fk_estudiantes_user_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
