<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrantes', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);
            $table->string('legajo', 30);
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('estado_civil', 50)->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->string('cuilt', 11)->nullable();
            $table->string('sexo_registral', 1)->nullable();
            $table->string('domicilio', 200)->nullable();
            $table->string('cpa', 10)->nullable();
            $table->string('telefono', 16)->nullable();
            $table->string('celular', 16)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('titulo_habilitante', 200)->nullable();
            $table->string('numero_registro', 50)->nullable();
            $table->string('art', 100)->nullable();
            $table->string('obra_social', 100)->nullable();
            $table->string('cuenta_bancaria', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_residencia_provincia')->nullable();
            $table->boolean('tiene_aportes')->default(false);
            $table->text('horario')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja', 500)->nullable();
            $table->string('foto_ruta')->nullable();
            $table->unsignedTinyInteger('tipo_documento_id')->nullable();
            $table->index('tipo_documento_id', 'int_tipo_documento_id_idx');
            $table->foreign('tipo_documento_id', 'int_tipo_documento_id_fk')->references('id')->on('tipos_documento')->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->index('ciudad_id', 'int_ciudad_id_idx');
            $table->foreign('ciudad_id', 'int_ciudad_id_fk')->references('id')->on('ciudades')->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('ciudad_nacimiento_id')->nullable();
            $table->index('ciudad_nacimiento_id', 'int_ciudad_nacimiento_id_idx');
            $table->foreign('ciudad_nacimiento_id', 'int_ciudad_nacimiento_id_fk')->references('id')->on('ciudades')->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedSmallInteger('nacionalidad_id')->nullable();
            $table->index('nacionalidad_id', 'int_nacionalidad_id_idx');
            $table->foreign('nacionalidad_id', 'int_nacionalidad_id_fk')->references('id')->on('nacionalidades')->restrictOnDelete()->restrictOnUpdate();
            $table->unique('legajo', 'int_legajo_unique');
            $table->index(['activo', 'apellido', 'nombre'], 'int_activo_nombre_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrantes');
    }
};
