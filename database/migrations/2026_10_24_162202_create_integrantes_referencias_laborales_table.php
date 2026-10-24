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
        Schema::create('integrantes_referencias_laborales', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('integrante_id');
            $table->string('lugar', 150);
            $table->string('cargo', 150)->nullable();
            $table->string('tiempo', 150)->nullable();
            $table->string('referencia_1_nombre', 150)->nullable();
            $table->string('referencia_1_telefono', 16)->nullable();
            $table->string('referencia_2_nombre', 150)->nullable();
            $table->string('referencia_2_telefono', 16)->nullable();
            $table->text('observaciones')->nullable();
            $table->index(['integrante_id', 'activo'], 'irl_integrante_activo_idx');
            $table->foreign('integrante_id', 'irl_integrante_fk')->references('id')->on('integrantes')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrantes_referencias_laborales');
    }
};
