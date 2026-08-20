<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'provincia_id', 'nombre', 'codigo_postal'])]
class Ciudad extends Model
{
    protected $table = 'ciudades';

    public $timestamps = false;

    protected $attributes = [
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'provincia_id' => 'integer',
        ];
    }

    /** @return BelongsTo<Provincia, $this> */
    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    /** @return HasMany<Alumno, $this> */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    /** @return HasMany<Alumno, $this> */
    public function alumnosNacidos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'ciudad_nacimiento_id');
    }

    /** @return HasMany<Responsable, $this> */
    public function responsables(): HasMany
    {
        return $this->hasMany(Responsable::class);
    }

    /** @return HasMany<Responsable, $this> */
    public function responsablesLaborales(): HasMany
    {
        return $this->hasMany(Responsable::class, 'ciudad_laboral_id');
    }
}
