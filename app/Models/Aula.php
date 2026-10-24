<?php

namespace App\Models;

use Database\Factories\AulaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['activo', 'descripcion', 'descripcion_larga', 'capacidad', 'es_especial', 'especialidad'])]
class Aula extends Model
{
    /** @use HasFactory<AulaFactory> */
    use HasFactory;

    protected $table = 'aulas';

    public $timestamps = false;

    protected $attributes = ['activo' => true, 'es_especial' => false];

    /** @return BelongsToMany<Adjunto, $this> */
    public function adjuntos(): BelongsToMany
    {
        return $this->belongsToMany(Adjunto::class, 'aulas_adjuntos');
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'capacidad' => 'integer',
            'es_especial' => 'boolean',
        ];
    }
}
