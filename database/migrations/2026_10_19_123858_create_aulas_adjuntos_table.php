<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aula_id');
            $table->unsignedBigInteger('adjunto_id');

            $table->index('aula_id', 'aulas_adjuntos_aula_idx');
            $table->unique('adjunto_id', 'aulas_adjuntos_adjunto_unique');
            $table->foreign('aula_id', 'aulas_adjuntos_aula_fk')
                ->references('id')->on('aulas')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('adjunto_id', 'aulas_adjuntos_adjunto_fk')
                ->references('id')->on('adjuntos')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas_adjuntos');
    }
};
