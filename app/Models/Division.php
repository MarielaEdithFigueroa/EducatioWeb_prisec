<?php

namespace App\Models;

use Database\Factories\DivisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'nivel_id', 'descripcion', 'orden'])]
class Division extends Model
{
    /** @use HasFactory<DivisionFactory> */
    use HasFactory;

    protected $table = 'divisiones';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'orden' => 'integer'];
    }

    /** @param Builder<Division> $query */
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
