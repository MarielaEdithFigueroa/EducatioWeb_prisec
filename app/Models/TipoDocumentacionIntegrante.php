<?php

namespace App\Models;

use Database\Factories\TipoDocumentacionIntegranteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['activo', 'descripcion'])]
class TipoDocumentacionIntegrante extends Model
{
    /** @use HasFactory<TipoDocumentacionIntegranteFactory> */
    use HasFactory;

    protected $table = 'tipos_documentacion_integrante';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
