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
        Schema::create('integrantes_contactos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('integrante_id');
            $table->string('nombre', 150);
            $table->string('telefono', 16)->nullable();
            $table->string('vinculo', 40)->nullable();
            $table->text('observaciones')->nullable();
            $table->index(['integrante_id', 'activo'], 'ic_integrante_activo_idx');
            $table->foreign('integrante_id', 'ic_integrante_fk')->references('id')->on('integrantes')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrantes_contactos');
    }
};
