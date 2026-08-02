<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->comment('Identificador público para URL de descarga firmada');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Usuario que subió el archivo');
            $table->string('archivable_type', 120)->comment('Clase del modelo dueño');
            $table->unsignedBigInteger('archivable_id');
            $table->string('tipo_documento', 60)
                ->comment('Programa del curso, Certificación de calificación, Constancia, ...');
            $table->string('nombre_original', 255);
            $table->string('disco', 30)->default('local');
            $table->string('ruta', 255);
            $table->string('mime_type', 100);
            $table->unsignedInteger('tamano_bytes');
            $table->char('hash_sha256', 64)->comment('Integridad y detección de duplicados');
            $table->timestamps();

            $table->unique('uuid', 'archivos_uuid_unique');
            $table->unique(['disco', 'ruta'], 'archivos_disco_ruta_unique');
            $table->index(['archivable_type', 'archivable_id'], 'archivos_archivable_index');
            $table->index('tipo_documento', 'archivos_tipo_documento_index');
            $table->index('user_id', 'archivos_user_id_index');

            $table->foreign('user_id', 'fk_archivos_user_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE archivos ADD CONSTRAINT chk_archivos_tamano
            CHECK (tamano_bytes > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
