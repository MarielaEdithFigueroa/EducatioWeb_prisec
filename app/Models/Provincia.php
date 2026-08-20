<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'codigo', 'nombre'])]
class Provincia extends Model
{
    protected $table = 'provincias';

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

    /** @return HasMany<Ciudad, $this> */
    public function ciudades(): HasMany
    {
        return $this->hasMany(Ciudad::class);
    }
}
