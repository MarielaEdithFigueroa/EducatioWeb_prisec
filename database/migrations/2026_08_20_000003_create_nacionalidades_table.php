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
        Schema::create('nacionalidades', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->boolean('activo')->default(true);
            $table->char('codigo', 3);
            $table->string('nombre', 100);

            $table->unique('codigo', 'nacionalidades_codigo_unique');
            $table->unique('nombre', 'nacionalidades_nombre_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nacionalidades');
    }
};
