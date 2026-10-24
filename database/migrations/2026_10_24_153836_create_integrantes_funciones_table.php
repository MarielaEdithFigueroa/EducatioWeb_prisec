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
        Schema::create('integrantes_funciones', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('integrante_id');
            $table->unsignedBigInteger('funcion_equipo_id');
            $table->index('funcion_equipo_id', 'ifn_funcion_idx');
            $table->unique(['integrante_id', 'funcion_equipo_id'], 'ifn_integrante_funcion_unique');
            $table->foreign('integrante_id', 'ifn_integrante_fk')->references('id')->on('integrantes')->restrictOnDelete();
            $table->foreign('funcion_equipo_id', 'ifn_funcion_fk')->references('id')->on('funciones_equipo')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrantes_funciones');
    }
};
