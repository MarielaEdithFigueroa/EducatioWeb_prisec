<?php

namespace App\Models;

use App\EstadoAnioLectivo;
use Database\Factories\AnioLectivoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'anio', 'estado'])]
class AnioLectivo extends Model
{
    /** @use HasFactory<AnioLectivoFactory> */
    use HasFactory;

    protected $table = 'anio_lectivos';

    public $timestamps = false;

    protected $attributes = ['activo' => true, 'estado' => EstadoAnioLectivo::Preparacion->value];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'anio' => 'integer', 'estado' => EstadoAnioLectivo::class];
    }

    /** @param Builder<AnioLectivo> $query */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    /** @return HasMany<Grupo, $this> */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }
}
