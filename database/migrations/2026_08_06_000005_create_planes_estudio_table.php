<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_estudio', function (Blueprint $table) {
            $table->bigIncrements('id_plan_estudio');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 150);
            $table->unsignedTinyInteger('id_nivel');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['id_nivel', 'activo', 'orden'], 'planes_nivel_activo_orden_idx');
            $table->unique(['id_plan_estudio', 'id_nivel'], 'planes_id_nivel_unique');
            $table->foreign('id_nivel', 'planes_nivel_fk')
                ->references('id_nivel')
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
