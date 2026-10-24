<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->string('nombre_original');
            $table->string('disco', 50);
            $table->string('ruta');
            $table->string('mime', 150);
            $table->unsignedBigInteger('tamano_bytes');
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_registro');
            $table->unsignedBigInteger('usuario_id');

            $table->unique(['disco', 'ruta'], 'adjuntos_archivo_unique');
            $table->index('usuario_id', 'adjuntos_usuario_idx');
            $table->foreign('usuario_id', 'adjuntos_usuario_fk')
                ->references('id')->on('users')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjuntos');
    }
};
