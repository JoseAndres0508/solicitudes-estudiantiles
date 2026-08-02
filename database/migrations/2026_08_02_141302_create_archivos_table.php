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
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Identificador público para URL de descarga firmada');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('archivable_type', 120);
            $table->unsignedBigInteger('archivable_id');
            $table->string('tipo_documento', 60);
            $table->string('nombre_original', 255);
            $table->string('disco', 30)->default('local');
            $table->string('ruta', 255);
            $table->string('mime_type', 100);
            $table->unsignedInteger('tamano_bytes');
            $table->char('hash_sha256', 64)->comment('Integridad y detección de duplicados');
            $table->timestamps();

            $table->unique(['disco', 'ruta']);
            $table->index(['archivable_type', 'archivable_id']);
            $table->index('tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
