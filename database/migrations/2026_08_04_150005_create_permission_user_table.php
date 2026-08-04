<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('granted_by')->nullable()
                ->comment('Quién concedió el permiso extra');
            $table->timestamp('created_at')->nullable();

            $table->primary(['user_id', 'permission_id']);
            $table->index('permission_id', 'permission_user_permission_id_index');
            $table->index('granted_by', 'permission_user_granted_by_index');

            $table->foreign('user_id', 'fk_permission_user_user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('permission_id', 'fk_permission_user_permission_id')
                ->references('id')->on('permissions')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('granted_by', 'fk_permission_user_granted_by')
                ->references('id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
