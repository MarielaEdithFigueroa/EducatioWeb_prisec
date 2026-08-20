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
        Schema::create('alumnos_responsables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(true);

            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('responsable_id');

            $table->unsignedTinyInteger('vinculo_id');
            $table->unsignedTinyInteger('orden_contacto')->default(0);

            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();

            $table->boolean('es_responsable_legal')->default(false);
            $table->boolean('es_responsable_pedagogico')->default(false);
            $table->boolean('es_responsable_economico')->default(false);

            $table->boolean('recibe_comunicaciones')->default(false);
            $table->boolean('recibe_informes_academicos')->default(false);
            $table->boolean('recibe_facturacion')->default(false);

            $table->boolean('es_contacto_principal')->default(false);
            $table->boolean('es_contacto_emergencia')->default(false);
            $table->boolean('convive_con_alumno')->default(false);
            $table->boolean('autorizado_retirar')->default(false);

            $table->boolean('habilitado_portal')->default(false);

            $table->text('observaciones')->nullable();

            $table->unique(
                ['alumno_id', 'responsable_id'],
                'alum_resp_alumno_resp_unique'
            );
            $table->index(
                ['alumno_id', 'activo', 'orden_contacto'],
                'alum_resp_alum_act_ord_idx'
            );
            $table->index(
                ['responsable_id', 'activo'],
                'alum_resp_resp_activo_idx'
            );
            $table->index('vinculo_id', 'alum_resp_vinculo_idx');

            $table->foreign('alumno_id', 'alum_resp_alumno_fk')
                ->references('id')
                ->on('alumnos')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('responsable_id', 'alum_resp_responsable_fk')
                ->references('id')
                ->on('responsables')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('vinculo_id', 'alum_resp_vinculo_fk')
                ->references('id')
                ->on('tipos_vinculo')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos_responsables');
    }
};
