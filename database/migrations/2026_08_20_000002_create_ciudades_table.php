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
        Schema::create('ciudades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->unsignedTinyInteger('provincia_id');
            $table->string('nombre', 120);
            $table->string('codigo_postal', 10)->nullable();

            $table->unique(
                ['provincia_id', 'nombre'],
                'ciudades_provincia_nombre_unique'
            );
            $table->index(
                ['provincia_id', 'activo', 'nombre'],
                'ciudades_prov_activo_nombre_idx'
            );

            $table->foreign('provincia_id', 'ciudades_provincia_fk')
                ->references('id')
                ->on('provincias')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
