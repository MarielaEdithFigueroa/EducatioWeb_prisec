<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'descripcion', 'nivel_id', 'orden'])]
class PlanEstudio extends Model
{
    protected $table = 'planes_estudio';

    public $timestamps = false;

    protected $attributes = [
        'activo' => true,
        'orden' => 0,
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
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
