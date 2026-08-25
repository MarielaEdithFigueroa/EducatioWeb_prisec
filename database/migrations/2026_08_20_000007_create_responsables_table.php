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
        Schema::create('responsables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);

            $table->string('apellido', 100);
            $table->string('nombre', 100);
            $table->string('nombre_elegido', 100)->nullable();
            $table->unsignedTinyInteger('tipo_documento_id')->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->char('cuilt', 11)->nullable();
            $table->date('fecha_nacimiento')->nullable();

            $table->char('sexo_registral', 1)->nullable();
            $table->string('genero', 30)->nullable();
            $table->string('genero_autodescripcion', 100)->nullable();

            $table->string('email', 254)->nullable();
            $table->string('telefono_e164', 20)->nullable();
            $table->string('telefono_original', 50)->nullable();

            $table->string('domicilio', 200)->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->string('cpa', 10)->nullable();

            $table->string('profesion', 100)->nullable();
            $table->string('domicilio_laboral', 200)->nullable();
            $table->unsignedBigInteger('ciudad_laboral_id')->nullable();
            $table->string('cpa_laboral', 10)->nullable();
            $table->string('telefono_laboral_e164', 20)->nullable();
            $table->string('telefono_laboral_original', 50)->nullable();

            $table->text('observaciones')->nullable();

            $table->unique(
                ['tipo_documento_id', 'numero_documento'],
                'responsables_documento_unique'
            );
            $table->unique('cuilt', 'responsables_cuilt_unique');
            $table->index(
                ['activo', 'apellido', 'nombre'],
                'responsables_activo_nombre_idx'
            );
            $table->index('ciudad_id', 'responsables_ciudad_idx');
            $table->index(
                'ciudad_laboral_id',
                'responsables_ciudad_lab_idx'
            );
            $table->index(
                'tipo_documento_id',
                'responsables_tipo_documento_idx'
            );

            $table->foreign('ciudad_id', 'responsables_ciudad_fk')
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign(
                'ciudad_laboral_id',
                'responsables_ciudad_lab_fk'
            )
                ->references('id')
                ->on('ciudades')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign(
                'tipo_documento_id',
                'responsables_tipo_documento_fk'
            )
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
        Schema::dropIfExists('responsables');
    }
};
