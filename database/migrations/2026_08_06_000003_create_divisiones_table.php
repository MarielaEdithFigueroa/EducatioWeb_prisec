<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisiones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);
            $table->string('descripcion', 50);
            $table->unsignedSmallInteger('orden')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisiones');
    }
};
