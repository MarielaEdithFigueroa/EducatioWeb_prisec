<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'activo',
    'apellido',
    'nombre',
    'nombre_elegido',
    'tipo_documento_id',
    'numero_documento',
    'cuilt',
    'fecha_nacimiento',
    'sexo_registral',
    'genero',
    'genero_autodescripcion',
    'email',
    'telefono_e164',
    'telefono_original',
    'domicilio',
    'ciudad_id',
    'cpa',
    'profesion',
    'domicilio_laboral',
    'ciudad_laboral_id',
    'cpa_laboral',
    'telefono_laboral_e164',
    'telefono_laboral_original',
    'observaciones',
])]
class Responsable extends Model
{
    protected $table = 'responsables';

    public $timestamps = false;

    protected $attributes = [
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'tipo_documento_id' => 'integer',
            'fecha_nacimiento' => 'date',
            'ciudad_id' => 'integer',
            'ciudad_laboral_id' => 'integer',
        ];
    }

    /** @return BelongsTo<TipoDocumento, $this> */
    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    /** @return BelongsTo<Ciudad, $this> */
    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class);
    }

    /** @return BelongsTo<Ciudad, $this> */
    public function ciudadLaboral(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_laboral_id');
    }

    /** @return HasMany<AlumnoResponsable, $this> */
    public function alumnosResponsables(): HasMany
    {
        return $this->hasMany(AlumnoResponsable::class);
    }
}
