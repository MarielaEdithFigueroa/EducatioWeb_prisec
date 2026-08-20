<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'descripcion'])]
class CondicionEspecial extends Model
{
    protected $table = 'condiciones_especiales';

    public $timestamps = false;

    protected $attributes = [
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /** @return HasMany<AlumnoCondicionEspecial, $this> */
    public function alumnosCondicionesEspeciales(): HasMany
    {
        return $this->hasMany(AlumnoCondicionEspecial::class, 'condicion_especial_id');
    }
}
