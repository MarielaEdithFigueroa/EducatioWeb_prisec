<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->foreignId('anio_lectivo_id');
            $table->foreignId('curso_id');
            $table->foreignId('division_id');
            $table->foreignId('turno_id');

            $table->unique(['anio_lectivo_id', 'curso_id', 'division_id', 'turno_id'], 'grupos_unq');

            // Nivel se deriva vía curso: no se guarda acá.
            $table->foreign('anio_lectivo_id', 'grupos_anio_fk')->references('id')->on('anios_lectivos');
            $table->foreign('curso_id', 'grupos_curso_fk')->references('id')->on('cursos');
            $table->foreign('division_id', 'grupos_division_fk')->references('id')->on('divisiones');
            $table->foreign('turno_id', 'grupos_turno_fk')->references('id')->on('turnos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
