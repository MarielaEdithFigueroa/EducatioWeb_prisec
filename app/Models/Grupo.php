<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activo', 'anio_lectivo_id', 'plan_estudio_id', 'curso_id', 'division_id', 'turno_id'])]
class Grupo extends Model
{
    /** @use HasFactory<GrupoFactory> */
    use HasFactory;

    protected $table = 'grupos';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** @param Builder<Grupo> $query */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    /** @return BelongsTo<AnioLectivo, $this> */
    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class);
    }

    /** @return BelongsTo<PlanEstudio, $this> */
    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class);
    }

    /** @return BelongsTo<Curso, $this> */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /** @return BelongsTo<Division, $this> */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /** @return BelongsTo<Turno, $this> */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }
}
