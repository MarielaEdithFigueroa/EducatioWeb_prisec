<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveles', function (Blueprint $table) {
            $table->unsignedTinyInteger('id');
            $table->string('codigo', 20);
            $table->string('descripcion', 50);

            $table->primary('id', 'niveles_pk');
            $table->unique('codigo', 'niveles_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};
