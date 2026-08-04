<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->comment('Identificador público para URL de descarga firmada');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Usuario que subió el archivo');
            $table->string('attachable_type', 120)->comment('Clase del modelo dueño');
            $table->unsignedBigInteger('attachable_id');
            $table->string('document_type', 60)
                ->comment('Programa del curso, Certificación de calificación, Constancia, ...');
            $table->string('original_name', 255);
            $table->string('disk', 30)->default('local');
            $table->string('path', 255);
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->char('sha256_hash', 64)->comment('Integridad y detección de duplicados');
            $table->timestamps();

            $table->unique('uuid', 'attachments_uuid_unique');
            $table->unique(['disk', 'path'], 'attachments_disk_path_unique');
            $table->index(['attachable_type', 'attachable_id'], 'attachments_attachable_index');
            $table->index('document_type', 'attachments_document_type_index');
            $table->index('user_id', 'attachments_user_id_index');

            $table->foreign('user_id', 'fk_attachments_user_id')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE attachments ADD CONSTRAINT chk_attachments_size
            CHECK (size_bytes > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
