<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['integrante_id', 'funcion_equipo_id', 'activo'])]
class IntegranteFuncion extends Model
{
    protected $table = 'integrantes_funciones';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
