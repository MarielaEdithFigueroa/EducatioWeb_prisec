<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('entidad', 64);
            $table->unsignedBigInteger('entidad_id');
            $table->string('accion', 16);
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('login', 64)->nullable();
            $table->string('session_id', 64)->nullable();
            $table->json('anterior')->nullable();
            $table->json('nuevo')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('origen', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entidad', 'entidad_id'], 'idx_logs_entidad_registro_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
