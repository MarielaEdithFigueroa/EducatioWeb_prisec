<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('adjunto_id');

            $table->index('alumno_id', 'alumnos_adjuntos_alumno_idx');
            $table->unique('adjunto_id', 'alumnos_adjuntos_adjunto_unique');
            $table->foreign('alumno_id', 'alumnos_adjuntos_alumno_fk')
                ->references('id')->on('alumnos')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('adjunto_id', 'alumnos_adjuntos_adjunto_fk')
                ->references('id')->on('adjuntos')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos_adjuntos');
    }
};
