<?php

namespace App\Models;

use Database\Factories\IntegranteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['legajo', 'apellido', 'nombre', 'estado_civil', 'numero_documento', 'cuilt', 'sexo_registral', 'domicilio', 'cpa', 'telefono', 'celular', 'email', 'titulo_habilitante', 'numero_registro', 'art', 'obra_social', 'cuenta_bancaria', 'fecha_nacimiento', 'fecha_ingreso', 'fecha_residencia_provincia', 'horario', 'tipo_documento_id', 'ciudad_id', 'ciudad_nacimiento_id', 'nacionalidad_id', 'tiene_aportes', 'activo', 'fecha_baja', 'motivo_baja', 'foto_ruta'])]
class Integrante extends Model
{
    /** @use HasFactory<IntegranteFactory> */
    use HasFactory;

    public const ESTADOS_CIVILES = ['Soltero/a', 'Casado/a', 'Divorciado/a', 'Separado/a', 'Viudo/a', 'Unión convivencial'];

    public const TELEFONOS_CON_TIPO = ['telefono'];

    protected $table = 'integrantes';

    public $timestamps = false;

    protected $attributes = ['activo' => true, 'tiene_aportes' => false];

    protected $hidden = ['foto_ruta'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean', 'tiene_aportes' => 'boolean',
            'fecha_nacimiento' => 'date:Y-m-d',
            'fecha_ingreso' => 'date:Y-m-d',
            'fecha_residencia_provincia' => 'date:Y-m-d',
            'fecha_baja' => 'date:Y-m-d',
            'tipo_documento_id' => 'integer',
            'ciudad_id' => 'integer',
            'ciudad_nacimiento_id' => 'integer',
            'nacionalidad_id' => 'integer',
        ];
    }

    /** @return BelongsTo<TipoDocumento, $this> */
    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    /** @return BelongsTo<Ciudad, $this> */
    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    /** @return BelongsTo<Ciudad, $this> */
    public function ciudadNacimiento(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_nacimiento_id');
    }

    /** @return BelongsTo<Nacionalidad, $this> */
    public function nacionalidad(): BelongsTo
    {
        return $this->belongsTo(Nacionalidad::class, 'nacionalidad_id');
    }

    /** @return BelongsToMany<FuncionEquipo, $this> */
    public function funciones(): BelongsToMany
    {
        return $this->belongsToMany(FuncionEquipo::class, 'integrantes_funciones', 'integrante_id', 'funcion_equipo_id')
            ->wherePivot('activo', true)->orderBy('orden');
    }

    /** @return HasMany<IntegranteFuncion, $this> */
    public function asignacionesFunciones(): HasMany
    {
        return $this->hasMany(IntegranteFuncion::class);
    }

    /** @return HasMany<ReferenciaLaboralIntegrante, $this> */
    public function referenciasLaborales(): HasMany
    {
        return $this->hasMany(ReferenciaLaboralIntegrante::class, 'integrante_id');
    }

    /** @return HasMany<ContactoIntegrante, $this> */
    public function contactos(): HasMany
    {
        return $this->hasMany(ContactoIntegrante::class, 'integrante_id');
    }

    /** @return HasMany<IntegranteDocumentacion, $this> */
    public function documentacion(): HasMany
    {
        return $this->hasMany(IntegranteDocumentacion::class);
    }

    /** @return BelongsToMany<Adjunto, $this> */
    public function adjuntos(): BelongsToMany
    {
        return $this->belongsToMany(Adjunto::class, 'integrantes_adjuntos', 'integrante_id', 'adjunto_id');
    }
}
