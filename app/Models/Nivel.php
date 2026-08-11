<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'codigo', 'descripcion'])]
class Nivel extends Model
{
    protected $table = 'niveles';

    public $incrementing = false;

    public $timestamps = false;

    /** @return HasMany<Curso, $this> */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }

    /** @return HasMany<PlanEstudio, $this> */
    public function planesEstudio(): HasMany
    {
        return $this->hasMany(PlanEstudio::class);
    }

    /** @return HasMany<Grupo, $this> */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }
}
