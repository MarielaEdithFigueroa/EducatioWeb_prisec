<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->string('codigo', 16);
            $table->string('descripcion', 128);

            $table->unique('codigo', 'turnos_codigo_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
