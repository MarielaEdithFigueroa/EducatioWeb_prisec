<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->bigIncrements('id_grupo');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_turno');
            $table->unsignedBigInteger('id_division');
            $table->unsignedBigInteger('id_plan_estudio')->nullable();
            $table->unsignedTinyInteger('id_nivel');
            $table->unsignedSmallInteger('ciclo_lectivo');
            $table->timestamps();

            $table->unique(
                ['ciclo_lectivo', 'id_nivel', 'id_curso', 'id_division', 'id_turno'],
                'grupos_ciclo_composicion_unique'
            );
            $table->index(['id_curso', 'id_nivel'], 'grupos_curso_nivel_idx');
            $table->index('id_turno', 'grupos_turno_idx');
            $table->index('id_division', 'grupos_division_idx');
            $table->index(['id_plan_estudio', 'id_nivel'], 'grupos_plan_nivel_idx');
            $table->index(['id_nivel', 'ciclo_lectivo', 'activo'], 'grupos_nivel_ciclo_activo_idx');

            $table->foreign('id_nivel', 'grupos_nivel_fk')
                ->references('id_nivel')
                ->on('niveles')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('id_turno', 'grupos_turno_fk')
                ->references('id_turno')
                ->on('turnos')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('id_division', 'grupos_division_fk')
                ->references('id_division')
                ->on('divisiones')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign(['id_curso', 'id_nivel'], 'grupos_curso_nivel_fk')
                ->references(['id_curso', 'id_nivel'])
                ->on('cursos')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign(['id_plan_estudio', 'id_nivel'], 'grupos_plan_nivel_fk')
                ->references(['id_plan_estudio', 'id_nivel'])
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
