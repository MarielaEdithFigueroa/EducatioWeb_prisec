<?php

namespace App\Models;

use Database\Factories\FuncionEquipoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['codigo', 'descripcion', 'orden'])]
class FuncionEquipo extends Model
{
    /** @use HasFactory<FuncionEquipoFactory> */
    use HasFactory;

    protected $table = 'funciones_equipo';

    public $timestamps = false;

    protected $hidden = ['pivot'];
}
