<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activo', 'id_curso', 'id_turno', 'id_division', 'id_plan_estudio', 'id_nivel', 'ciclo_lectivo'])]
class Grupo extends Model
{
    protected $table = 'grupos';

    protected $primaryKey = 'id_grupo';

    protected $keyType = 'int';

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
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    /** @return BelongsTo<Turno, $this> */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'id_turno', 'id_turno');
    }

    /** @return BelongsTo<Division, $this> */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'id_division', 'id_division');
    }

    /** @return BelongsTo<PlanEstudio, $this> */
    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan_estudio', 'id_plan_estudio');
    }

    /** @return BelongsTo<Nivel, $this> */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }
}
