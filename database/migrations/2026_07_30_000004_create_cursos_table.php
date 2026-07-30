<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->foreignId('nivel_id');
            $table->string('codigo', 16);
            $table->string('descripcion', 128);
            $table->unsignedInteger('orden')->default(0);

            $table->foreign('nivel_id', 'cursos_nivel_fk')->references('id')->on('niveles');
            $table->index('nivel_id', 'cursos_nivel_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
