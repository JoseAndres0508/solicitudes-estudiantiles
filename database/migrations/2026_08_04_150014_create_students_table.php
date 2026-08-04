<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()
                ->comment('Cuenta de acceso al portal');
            $table->string('national_id', 12);
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            $table->string('second_last_name', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('national_id', 'students_national_id_unique');
            $table->unique('user_id', 'students_user_id_unique');
            $table->index(['last_name', 'second_last_name'], 'students_last_names_index');

            $table->foreign('user_id', 'fk_students_user_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
