<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'activo',
    'legajo',
    'apellido',
    'nombre',
    'nombre_elegido',
    'tipo_documento_id',
    'numero_documento',
    'cuilt',
    'fecha_nacimiento',
    'ciudad_nacimiento_id',
    'nacionalidad_id',
    'grupo_sanguineo_id',
    'sexo_registral',
    'genero',
    'genero_autodescripcion',
    'email',
    'domicilio',
    'ciudad_id',
    'cpa',
    'fecha_ingreso',
    'fecha_inicio_cursado',
    'libro',
    'folio',
    'fecha_baja',
    'motivo_baja_id',
    'autoriza_uso_imagen',
    'observaciones',
])]
class Alumno extends Model
{
    protected $table = 'alumnos';

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
            'ciudad_nacimiento_id' => 'integer',
            'nacionalidad_id' => 'integer',
            'grupo_sanguineo_id' => 'integer',
            'ciudad_id' => 'integer',
            'fecha_ingreso' => 'date',
            'fecha_inicio_cursado' => 'date',
            'fecha_baja' => 'date',
            'motivo_baja_id' => 'integer',
            'autoriza_uso_imagen' => 'boolean',
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
    public function ciudadNacimiento(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_nacimiento_id');
    }

    /** @return BelongsTo<Nacionalidad, $this> */
    public function nacionalidad(): BelongsTo
    {
        return $this->belongsTo(Nacionalidad::class);
    }

    /** @return BelongsTo<GrupoSanguineo, $this> */
    public function grupoSanguineo(): BelongsTo
    {
        return $this->belongsTo(GrupoSanguineo::class);
    }

    /** @return BelongsTo<MotivoBaja, $this> */
    public function motivoBaja(): BelongsTo
    {
        return $this->belongsTo(MotivoBaja::class);
    }

    /** @return HasMany<AlumnoResponsable, $this> */
    public function alumnosResponsables(): HasMany
    {
        return $this->hasMany(AlumnoResponsable::class);
    }

    /** @return HasMany<AlumnoCondicionEspecial, $this> */
    public function alumnosCondicionesEspeciales(): HasMany
    {
        return $this->hasMany(AlumnoCondicionEspecial::class);
    }
}
