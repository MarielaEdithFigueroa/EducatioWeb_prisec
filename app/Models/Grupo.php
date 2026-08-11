<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activo', 'curso_id', 'turno_id', 'division_id', 'plan_estudio_id', 'nivel_id', 'ciclo_lectivo'])]
class Grupo extends Model
{
    protected $table = 'grupos';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'ciclo_lectivo' => 'integer',
        ];
    }

    /** @return BelongsTo<Curso, $this> */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /** @return BelongsTo<Turno, $this> */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    /** @return BelongsTo<Division, $this> */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /** @return BelongsTo<PlanEstudio, $this> */
    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class);
    }

    /** @return BelongsTo<Nivel, $this> */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class);
    }
}
