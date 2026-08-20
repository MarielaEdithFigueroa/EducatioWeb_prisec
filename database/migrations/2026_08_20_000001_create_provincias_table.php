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
        Schema::create('provincias', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('codigo', 4);
            $table->string('nombre', 100);

            $table->unique('codigo', 'provincias_codigo_unique');
            $table->unique('nombre', 'provincias_nombre_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provincias');
    }
};
