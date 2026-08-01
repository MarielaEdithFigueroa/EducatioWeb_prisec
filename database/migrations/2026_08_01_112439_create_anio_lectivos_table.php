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
        Schema::create('anio_lectivos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('anio');
            $table->string('estado', 16)->default('preparacion');

            $table->unique('anio', 'anios_lectivos_anio_unique');
            $table->index(['activo', 'estado', 'anio'], 'anios_activo_estado_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anio_lectivos');
    }
};
