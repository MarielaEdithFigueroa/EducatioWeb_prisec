<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['activo', 'integrante_id', 'tipo_documentacion_integrante_id', 'presentado'])]
class IntegranteDocumentacion extends Model
{
    protected $table = 'integrantes_documentacion';

    public $timestamps = false;

    protected $attributes = ['activo' => true, 'presentado' => false];

    /** @return BelongsToMany<Adjunto, $this> */
    public function adjuntos(): BelongsToMany
    {
        return $this->belongsToMany(Adjunto::class, 'integrantes_documentacion_adjuntos', 'integrante_documentacion_id', 'adjunto_id');
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'presentado' => 'boolean', 'tipo_documentacion_integrante_id' => 'integer'];
    }
}
