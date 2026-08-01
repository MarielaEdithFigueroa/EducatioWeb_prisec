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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->foreignId('anio_lectivo_id');
            $table->foreignId('plan_estudio_id');
            $table->foreignId('curso_id');
            $table->foreignId('division_id');
            $table->foreignId('turno_id');

            $table->foreign('anio_lectivo_id', 'grupos_anio_fk')->references('id')->on('anio_lectivos');
            $table->foreign('plan_estudio_id', 'grupos_plan_fk')->references('id')->on('planes_estudio');
            $table->foreign('curso_id', 'grupos_curso_fk')->references('id')->on('cursos');
            $table->foreign('division_id', 'grupos_division_fk')->references('id')->on('divisiones');
            $table->foreign('turno_id', 'grupos_turno_fk')->references('id')->on('turnos');
            $table->index('plan_estudio_id', 'grupos_plan_idx');
            $table->index('curso_id', 'grupos_curso_idx');
            $table->index('division_id', 'grupos_division_idx');
            $table->index('turno_id', 'grupos_turno_idx');
            $table->unique(
                ['anio_lectivo_id', 'plan_estudio_id', 'curso_id', 'division_id', 'turno_id'],
                'grupos_combinacion_unique',
            );
            $table->index(['anio_lectivo_id', 'activo'], 'grupos_anio_activo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
