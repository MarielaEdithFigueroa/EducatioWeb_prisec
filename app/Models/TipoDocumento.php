<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre'])]
class TipoDocumento extends Model
{
    protected $table = 'tipos_documento';

    public $timestamps = false;

    /** @return HasMany<Alumno, $this> */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    /** @return HasMany<Responsable, $this> */
    public function responsables(): HasMany
    {
        return $this->hasMany(Responsable::class);
    }
}
