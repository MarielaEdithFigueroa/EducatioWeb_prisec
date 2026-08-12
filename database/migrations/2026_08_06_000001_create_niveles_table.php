<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveles', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('codigo', 20);
            $table->string('descripcion', 50);

            $table->unique('codigo', 'niveles_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};
