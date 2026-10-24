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
        Schema::create('funciones_equipo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique('fe_codigo_unique');
            $table->string('descripcion', 100)->unique('fe_descripcion_unique');
            $table->unsignedSmallInteger('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funciones_equipo');
    }
};
