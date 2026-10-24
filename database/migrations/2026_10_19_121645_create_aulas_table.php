<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 100);
            $table->text('descripcion_larga')->nullable();
            $table->unsignedSmallInteger('capacidad')->nullable();
            $table->boolean('es_especial')->default(false);
            $table->string('especialidad', 100)->nullable();

            $table->unique('descripcion', 'aulas_descripcion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
