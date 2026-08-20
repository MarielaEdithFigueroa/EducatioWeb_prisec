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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);

            $table->string('legajo', 30);
            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('nombre_elegido', 100)->nullable();
            $table->unsignedTinyInteger('tipo_documento_id')->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->char('cuilt', 11)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->unsignedBigInteger('ciudad_nacimiento_id')->nullable();
            $table->unsignedSmallInteger('nacionalidad_id')->nullable();
            $table->unsignedTinyInteger('grupo_sanguineo_id')->nullable();

            $table->char('sexo_registral', 1)->nullable();
            $table->string('genero', 30)->nullable();
            $table->string('genero_autodescripcion', 100)->nullable();

            $table->string('email', 254)->nullable();

            $table->string('domicilio', 200)->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->char('cpa', 8)->nullable();

            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_inicio_cursado')->nullable();
            $table->string('libro', 30)->nullable();
            $table->string('folio', 30)->nullable();
            $table->date('fecha_baja')->nullable();
            $table->unsignedBigInteger('motivo_baja_id')->nullable();
            $table->boolean('autoriza_uso_imagen')->nullable();
            $table->text('observaciones')->nullable();

            $table->unique('legajo', 'alumnos_legajo_unique');
            $table->unique(
                ['tipo_documento_id', 'numero_documento'],
                'alumnos_documento_unique'
            );
            $table->unique('cuilt', 'alumnos_cuilt_unique');
            $table->index(
                ['activo', 'apellido', 'nombre'],
                'alumnos_activo_nombre_idx'
            );
            $table->index('ciudad_id', 'alumnos_ciudad_idx');
            $table->index(
                'ciudad_nacimiento_id',
                'alumnos_ciudad_nacimiento_idx'
            );
            $table->index('nacionalidad_id', 'alumnos_nacionalidad_idx');
            $table->index(
                'grupo_sanguineo_id',
                'alumnos_grupo_sanguineo_idx'
            );
            $table->index('motivo_baja_id', 'alumnos_motivo_baja_idx');
            $table->index('tipo_documento_id', 'alumnos_tipo_documento_idx');

            $table->foreign('ciudad_id', 'alumnos_ciudad_fk')
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign(
                'ciudad_nacimiento_id',
                'alumnos_ciudad_nacimiento_fk'
            )
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('nacionalidad_id', 'alumnos_nacionalidad_fk')
                ->references('id')
                ->on('nacionalidades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign(
                'grupo_sanguineo_id',
                'alumnos_grupo_sanguineo_fk'
            )
                ->references('id')
                ->on('grupos_sanguineos')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('motivo_baja_id', 'alumnos_motivo_baja_fk')
                ->references('id')
                ->on('motivos_baja')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('tipo_documento_id', 'alumnos_tipo_documento_fk')
                ->references('id')
                ->on('tipos_documento')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
