<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id_nivel', 'codigo', 'descripcion'])]
class Nivel extends Model
{
    protected $table = 'niveles';

    protected $primaryKey = 'id_nivel';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    /** @return HasMany<Curso, $this> */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'id_nivel', 'id_nivel');
    }

    /** @return HasMany<PlanEstudio, $this> */
    public function planesEstudio(): HasMany
    {
        return $this->hasMany(PlanEstudio::class, 'id_nivel', 'id_nivel');
    }

    /** @return HasMany<Grupo, $this> */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'id_nivel', 'id_nivel');
    }
}
