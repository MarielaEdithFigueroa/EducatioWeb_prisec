<?php

namespace App\Models;

use Database\Factories\AnioLectivoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['activo', 'anio', 'vigente'])]
class AnioLectivo extends Model
{
    /** @use HasFactory<AnioLectivoFactory> */
    use HasFactory;

    protected $table = 'anios_lectivos';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'anio' => 'integer',
            'vigente' => 'boolean',
        ];
    }

    /** @return HasMany<Grupo, $this> */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }
}
