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
        Schema::create('niveles', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->string('codigo', 8);
            $table->string('descripcion', 64);
            $table->unsignedTinyInteger('orden');

            $table->unique('codigo', 'niveles_codigo_unique');
            $table->unique('descripcion', 'niveles_descripcion_unique');
            $table->index(['activo', 'orden'], 'niveles_activo_orden_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};
