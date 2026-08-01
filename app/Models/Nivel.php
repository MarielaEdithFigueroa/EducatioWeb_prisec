<?php

namespace App\Models;

use Database\Factories\NivelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'codigo', 'descripcion', 'orden'])]
class Nivel extends Model
{
    /** @use HasFactory<NivelFactory> */
    use HasFactory;

    protected $table = 'niveles';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'orden' => 'integer'];
    }

    /** @param Builder<Nivel> $query */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    /** @return HasMany<Curso, $this> */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }

    /** @return HasMany<Division, $this> */
    public function divisiones(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /** @return HasMany<Turno, $this> */
    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    /** @return HasMany<PlanEstudio, $this> */
    public function planesEstudio(): HasMany
    {
        return $this->hasMany(PlanEstudio::class);
    }
}
