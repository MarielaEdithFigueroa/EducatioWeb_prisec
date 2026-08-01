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
        Schema::create('divisiones', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->foreignId('nivel_id');
            $table->string('descripcion', 64);
            $table->unsignedTinyInteger('orden');

            $table->foreign('nivel_id', 'divisiones_nivel_fk')->references('id')->on('niveles');
            $table->unique(['nivel_id', 'descripcion'], 'divisiones_nivel_desc_unique');
            $table->index(['nivel_id', 'activo', 'orden'], 'divisiones_nivel_activo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisiones');
    }
};
