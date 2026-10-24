<?php

namespace App\Models;

use Database\Factories\ReferenciaLaboralIntegranteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activo', 'integrante_id', 'lugar', 'cargo', 'tiempo', 'referencia_1_nombre', 'referencia_1_telefono', 'referencia_2_nombre', 'referencia_2_telefono', 'observaciones'])]
class ReferenciaLaboralIntegrante extends Model
{
    /** @use HasFactory<ReferenciaLaboralIntegranteFactory> */
    use HasFactory;

    protected $table = 'integrantes_referencias_laborales';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** @return BelongsTo<Integrante, $this> */
    public function integrante(): BelongsTo
    {
        return $this->belongsTo(Integrante::class);
    }
}
