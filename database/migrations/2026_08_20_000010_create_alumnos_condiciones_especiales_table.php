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
        Schema::create('alumnos_condiciones_especiales', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('alumno_id');
            $table->unsignedTinyInteger('condicion_especial_id');

            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();

            $table->index(
                ['alumno_id', 'condicion_especial_id'],
                'alum_cond_esp_alumno_condicion_idx'
            );
            $table->index('condicion_especial_id', 'alum_cond_esp_condicion_idx');
            $table->index('usuario_id', 'alum_cond_esp_usuario_idx');

            $table->foreign('alumno_id', 'alum_cond_esp_alumno_fk')
                ->references('id')
                ->on('alumnos')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('condicion_especial_id', 'alum_cond_esp_condicion_fk')
                ->references('id')
                ->on('condiciones_especiales')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('usuario_id', 'alum_cond_esp_usuario_fk')
                ->references('id')
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos_condiciones_especiales');
    }
};
