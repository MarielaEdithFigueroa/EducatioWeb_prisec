<?php

namespace App\Models;

use Database\Factories\ContactoIntegranteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activo', 'integrante_id', 'nombre', 'telefono', 'vinculo', 'observaciones'])]
class ContactoIntegrante extends Model
{
    /** @use HasFactory<ContactoIntegranteFactory> */
    use HasFactory;

    public const VINCULOS = ['Cónyuge', 'Familiar', 'Amistad', 'Colega', 'Otro'];

    protected $table = 'integrantes_contactos';

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
