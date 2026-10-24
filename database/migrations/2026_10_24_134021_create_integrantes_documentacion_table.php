<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrantes_documentacion', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('integrante_id');
            $table->unsignedBigInteger('tipo_documentacion_integrante_id');
            $table->boolean('presentado')->default(false);
            $table->unique(['integrante_id', 'tipo_documentacion_integrante_id'], 'idoc_integrante_tipo_unique');
            $table->index('tipo_documentacion_integrante_id', 'idoc_tipo_idx');
            $table->foreign('integrante_id', 'idoc_integrante_fk')->references('id')->on('integrantes')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('tipo_documentacion_integrante_id', 'idoc_tipo_fk')->references('id')->on('tipos_documentacion_integrante')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrantes_documentacion');
    }
};
