<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('curso_id');
            $table->unsignedBigInteger('turno_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('plan_estudio_id')->nullable();
            $table->unsignedTinyInteger('nivel_id');
            $table->unsignedSmallInteger('ciclo_lectivo');
            $table->timestamps();

            $table->unique(
                ['ciclo_lectivo', 'nivel_id', 'curso_id', 'division_id', 'turno_id'],
                'grupos_ciclo_composicion_unique'
            );
            $table->index(['curso_id', 'nivel_id'], 'grupos_curso_nivel_idx');
            $table->index('turno_id', 'grupos_turno_idx');
            $table->index('division_id', 'grupos_division_idx');
            $table->index(['plan_estudio_id', 'nivel_id'], 'grupos_plan_nivel_idx');
            $table->index(['nivel_id', 'ciclo_lectivo', 'activo'], 'grupos_nivel_ciclo_activo_idx');

            $table->foreign('nivel_id', 'grupos_nivel_fk')
                ->references('id')
                ->on('niveles')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('turno_id', 'grupos_turno_fk')
                ->references('id')
                ->on('turnos')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('division_id', 'grupos_division_fk')
                ->references('id')
                ->on('divisiones')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign(['curso_id', 'nivel_id'], 'grupos_curso_nivel_fk')
                ->references(['id', 'nivel_id'])
                ->on('cursos')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign(['plan_estudio_id', 'nivel_id'], 'grupos_plan_nivel_fk')
                ->references(['id', 'nivel_id'])
                ->on('planes_estudio')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
