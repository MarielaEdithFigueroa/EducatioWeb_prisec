<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anios_lectivos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('anio');
            $table->boolean('vigente')->default(false);

            $table->unique('anio', 'anios_lectivos_anio_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anios_lectivos');
    }
};
