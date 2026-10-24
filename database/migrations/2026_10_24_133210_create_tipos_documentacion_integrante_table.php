<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_documentacion_integrante', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 150);
            $table->unique('descripcion', 'tdi_descripcion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_documentacion_integrante');
    }
};
