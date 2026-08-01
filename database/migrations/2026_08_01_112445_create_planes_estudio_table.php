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
        Schema::create('planes_estudio', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->foreignId('nivel_id');
            $table->string('codigo', 32);
            $table->string('descripcion', 128);

            $table->foreign('nivel_id', 'planes_nivel_fk')->references('id')->on('niveles');
            $table->unique(['nivel_id', 'codigo'], 'planes_nivel_codigo_unique');
            $table->index(['nivel_id', 'activo', 'descripcion'], 'planes_nivel_activo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_estudio');
    }
};
