<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'activo',
    'alumno_id',
    'responsable_id',
    'vinculo_id',
    'orden_contacto',
    'fecha_desde',
    'fecha_hasta',
    'es_responsable_legal',
    'es_responsable_pedagogico',
    'es_responsable_economico',
    'recibe_comunicaciones',
    'recibe_informes_academicos',
    'recibe_facturacion',
    'es_contacto_principal',
    'es_contacto_emergencia',
    'convive_con_alumno',
    'autorizado_retirar',
    'habilitado_portal',
    'observaciones',
])]
class AlumnoResponsable extends Model
{
    protected $table = 'alumnos_responsables';

    public $timestamps = false;

    protected $attributes = [
        'activo' => true,
        'orden_contacto' => 0,
        'es_responsable_legal' => false,
        'es_responsable_pedagogico' => false,
        'es_responsable_economico' => false,
        'recibe_comunicaciones' => false,
        'recibe_informes_academicos' => false,
        'recibe_facturacion' => false,
        'es_contacto_principal' => false,
        'es_contacto_emergencia' => false,
        'convive_con_alumno' => false,
        'autorizado_retirar' => false,
        'habilitado_portal' => false,
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'alumno_id' => 'integer',
            'responsable_id' => 'integer',
            'vinculo_id' => 'integer',
            'orden_contacto' => 'integer',
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'es_responsable_legal' => 'boolean',
            'es_responsable_pedagogico' => 'boolean',
            'es_responsable_economico' => 'boolean',
            'recibe_comunicaciones' => 'boolean',
            'recibe_informes_academicos' => 'boolean',
            'recibe_facturacion' => 'boolean',
            'es_contacto_principal' => 'boolean',
            'es_contacto_emergencia' => 'boolean',
            'convive_con_alumno' => 'boolean',
            'autorizado_retirar' => 'boolean',
            'habilitado_portal' => 'boolean',
        ];
    }

    /** @return BelongsTo<Alumno, $this> */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /** @return BelongsTo<Responsable, $this> */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    /** @return BelongsTo<TipoVinculo, $this> */
    public function tipoVinculo(): BelongsTo
    {
        return $this->belongsTo(TipoVinculo::class, 'vinculo_id');
    }
}
