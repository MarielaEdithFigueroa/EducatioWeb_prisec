<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'alumno_id',
    'condicion_especial_id',
    'fecha_desde',
    'fecha_hasta',
    'usuario_id',
    'descripcion',
])]
class AlumnoCondicionEspecial extends Model
{
    protected $table = 'alumnos_condiciones_especiales';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'alumno_id' => 'integer',
            'condicion_especial_id' => 'integer',
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'usuario_id' => 'integer',
            'fecha_registro' => 'datetime',
        ];
    }

    /** @return BelongsTo<Alumno, $this> */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /** @return BelongsTo<CondicionEspecial, $this> */
    public function condicionEspecial(): BelongsTo
    {
        return $this->belongsTo(CondicionEspecial::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
