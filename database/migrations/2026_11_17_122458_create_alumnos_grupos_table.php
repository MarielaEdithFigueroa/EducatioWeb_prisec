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
        Schema::create('alumnos_grupos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('grupo_id');
            $table->text('grupo_descripcion');
            $table->dateTime('fecha_desde', 6);
            $table->dateTime('fecha_hasta', 6)->nullable();
            $table->string('motivo_ingreso', 500);
            $table->string('motivo_egreso', 500)->nullable();
            $table->unsignedBigInteger('alumno_vigente_id')
                ->nullable()->virtualAs('CASE WHEN fecha_hasta IS NULL THEN alumno_id ELSE NULL END');

            $table->unique('alumno_vigente_id', 'ag_alumno_vigente_unique');
            $table->index(['alumno_id', 'id'], 'ag_alumno_historial_idx');
            $table->index(['grupo_id', 'fecha_hasta'], 'ag_grupo_vigencia_idx');
            $table->foreign('alumno_id', 'ag_alumno_fk')->references('id')->on('alumnos')
                ->restrictOnUpdate()->restrictOnDelete();
            $table->foreign('grupo_id', 'ag_grupo_fk')->references('id')->on('grupos')
                ->restrictOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos_grupos');
    }
};
