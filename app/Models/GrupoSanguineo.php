<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['codigo', 'nombre'])]
class GrupoSanguineo extends Model
{
    protected $table = 'grupos_sanguineos';

    public $timestamps = false;

    /** @return HasMany<Alumno, $this> */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }
}
