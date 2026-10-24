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
        Schema::create('integrantes_documentacion_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('integrante_documentacion_id');
            $table->unsignedBigInteger('adjunto_id');
            $table->index('integrante_documentacion_id', 'ida_documentacion_idx');
            $table->unique('adjunto_id', 'ida_adjunto_unique');
            $table->foreign('integrante_documentacion_id', 'ida_documentacion_fk')
                ->references('id')->on('integrantes_documentacion')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('adjunto_id', 'ida_adjunto_fk')
                ->references('id')->on('adjuntos')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrantes_documentacion_adjuntos');
    }
};
