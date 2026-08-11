<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 100);
            $table->unsignedTinyInteger('nivel_id');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['nivel_id', 'activo', 'orden'], 'cursos_nivel_activo_orden_idx');
            $table->unique(['id', 'nivel_id'], 'cursos_id_nivel_unique');
            $table->foreign('nivel_id', 'cursos_nivel_fk')
                ->references('id')
                ->on('niveles')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
