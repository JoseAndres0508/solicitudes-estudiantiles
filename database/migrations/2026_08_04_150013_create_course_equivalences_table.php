<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_equivalences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_course_id')->comment('Curso del plan anterior');
            $table->unsignedBigInteger('target_course_id')->comment('Curso equivalente del plan nuevo');
            $table->enum('direction', ['Anterior a nuevo', 'Nuevo a anterior', 'Bidireccional']);
            $table->string('resolution_number', 60);
            $table->enum('status', ['Vigente', 'Sustituida'])->default('Vigente');
            $table->unsignedBigInteger('superseded_by_id')->nullable()
                ->comment('Equiparación que prevalece (RC-02)');
            $table->timestamps();

            $table->unique(
                ['source_course_id', 'target_course_id', 'resolution_number'],
                'course_equivalences_pair_resolution_unique'
            );
            $table->index('target_course_id', 'course_equivalences_target_course_index');
            $table->index('status', 'course_equivalences_status_index');
            $table->index('superseded_by_id', 'course_equivalences_superseded_by_index');

            $table->foreign('source_course_id', 'fk_course_equivalences_source_course_id')
                ->references('id')->on('courses')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('target_course_id', 'fk_course_equivalences_target_course_id')
                ->references('id')->on('courses')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('superseded_by_id', 'fk_course_equivalences_superseded_by_id')
                ->references('id')->on('course_equivalences')
                ->nullOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE course_equivalences ADD CONSTRAINT chk_course_equivalences_distinct
            CHECK (source_course_id <> target_course_id)');

        // Una equiparación Sustituida debe apuntar a la que la reemplaza;
        // una Vigente no debe apuntar a ninguna.
        DB::statement("ALTER TABLE course_equivalences ADD CONSTRAINT chk_course_equivalences_superseded
            CHECK ((status = 'Sustituida' AND superseded_by_id IS NOT NULL)
                OR (status = 'Vigente' AND superseded_by_id IS NULL))");
    }

    public function down(): void
    {
        Schema::dropIfExists('course_equivalences');
    }
};
