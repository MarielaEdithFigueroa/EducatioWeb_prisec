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
        Schema::create('grupos_sanguineos', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('codigo', 5);
            $table->string('nombre', 30);

            $table->unique('codigo', 'grupos_sanguineos_codigo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos_sanguineos');
    }
};
