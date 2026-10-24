<?php

namespace App\Models;

use Database\Factories\AdjuntoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['activo', 'nombre_original', 'disco', 'ruta', 'mime', 'tamano_bytes', 'observaciones', 'fecha_registro', 'usuario_id'])]
class Adjunto extends Model
{
    /** @use HasFactory<AdjuntoFactory> */
    use HasFactory;

    protected $table = 'adjuntos';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected $hidden = ['disco', 'ruta', 'pivot'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'tamano_bytes' => 'integer',
            'fecha_registro' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Alumno, $this> */
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'alumnos_adjuntos');
    }

    /** @return BelongsToMany<Aula, $this> */
    public function aulas(): BelongsToMany
    {
        return $this->belongsToMany(Aula::class, 'aulas_adjuntos');
    }
}
