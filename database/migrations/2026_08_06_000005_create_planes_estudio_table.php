<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_estudio', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 150);
            $table->unsignedTinyInteger('nivel_id');
            $table->unsignedSmallInteger('orden')->default(0);

            $table->index(['nivel_id', 'activo', 'orden'], 'planes_nivel_activo_orden_idx');
            $table->unique(['nivel_id', 'descripcion'], 'planes_nivel_descripcion_unique');
            $table->unique(['id', 'nivel_id'], 'planes_id_nivel_unique');
            $table->foreign('nivel_id', 'planes_nivel_fk')
                ->references('id')
                ->on('niveles')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_estudio');
    }
};
