<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_estados_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_id');
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->string('comentario', 255)->nullable()
                ->comment('Justificación del cambio; obligatoria en denegaciones (capa app)');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('notificado_at')->nullable()
                ->comment('Momento del correo al estudiante');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['solicitud_id', 'created_at'], 'solicitud_estados_solicitud_fecha_index');
            $table->index('user_id', 'solicitud_estados_user_id_index');

            $table->foreign('solicitud_id', 'fk_solicitud_estados_solicitud_id')
                ->references('id')->on('solicitudes')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_solicitud_estados_user_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_estados_historial');
    }
};
