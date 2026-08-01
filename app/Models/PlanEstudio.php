<?php

namespace App\Models;

use Database\Factories\PlanEstudioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'nivel_id', 'codigo', 'descripcion'])]
class PlanEstudio extends Model
{
    /** @use HasFactory<PlanEstudioFactory> */
    use HasFactory;

    protected $table = 'planes_estudio';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** @param Builder<PlanEstudio> $query */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    /** @return BelongsTo<Nivel, $this> */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class);
    }

    /** @return HasMany<Grupo, $this> */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }
}
