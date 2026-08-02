<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamp('created_at')->nullable();

            $table->primary(['role_id', 'permission_id']);
            $table->index('permission_id', 'permission_role_permission_id_index');

            $table->foreign('role_id', 'fk_permission_role_role_id')
                ->references('id')->on('roles')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('permission_id', 'fk_permission_role_permission_id')
                ->references('id')->on('permissions')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
