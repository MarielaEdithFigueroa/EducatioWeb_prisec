<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrantes_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('integrante_id');
            $table->unsignedBigInteger('adjunto_id');

            $table->index('integrante_id', 'integrantes_adjuntos_integrante_idx');
            $table->unique('adjunto_id', 'integrantes_adjuntos_adjunto_unique');
            $table->foreign('integrante_id', 'integrantes_adjuntos_integrante_fk')
                ->references('id')->on('integrantes')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('adjunto_id', 'integrantes_adjuntos_adjunto_fk')
                ->references('id')->on('adjuntos')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrantes_adjuntos');
    }
};
