<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_request_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_request_id');
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->string('comment', 255)->nullable()
                ->comment('Justificación del cambio; obligatoria en denegaciones (capa app)');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('notified_at')->nullable()
                ->comment('Momento del correo al estudiante');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['student_request_id', 'created_at'],
                'student_request_status_history_request_date_index');
            $table->index('user_id', 'student_request_status_history_user_id_index');

            $table->foreign('student_request_id', 'fk_student_request_status_history_request_id')
                ->references('id')->on('student_requests')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_student_request_status_history_user_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_request_status_history');
    }
};
