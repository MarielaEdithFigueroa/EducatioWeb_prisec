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
        Schema::create('condiciones_especiales', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 200);

            $table->unique('descripcion', 'condiciones_especiales_descripcion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condiciones_especiales');
    }
};
