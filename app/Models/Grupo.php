<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['activo', 'curso_id', 'turno_id', 'division_id', 'plan_estudio_id', 'nivel_id', 'ciclo_lectivo'])]
class Grupo extends Model
{
    /** @use HasFactory<GrupoFactory> */
    use HasFactory;

    protected $table = 'grupos';

    public $timestamps = false;

    protected $attributes = ['activo' => true];

    protected static function booted(): void
    {
        static::saving(function (Grupo $grupo): void {
            if (! $grupo->exists || $grupo->isDirty(['ciclo_lectivo', 'nivel_id', 'curso_id', 'division_id', 'turno_id'])) {
                $grupo->load(['nivel', 'curso', 'division', 'turno']);
                $grupo->dh_descripcion = $grupo->descripcion;
            }
        });
    }

    /** @return Attribute<string, never> */
    protected function descripcion(): Attribute
    {
        return Attribute::get(fn (): string => implode(' · ', [
            $this->ciclo_lectivo,
            $this->nivel->descripcion,
            $this->curso->descripcion.' '.$this->division->descripcion,
            $this->turno->descripcion,
        ]));
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'ciclo_lectivo' => 'integer',
            'nivel_id' => 'integer',
            'curso_id' => 'integer',
            'division_id' => 'integer',
            'turno_id' => 'integer',
            'plan_estudio_id' => 'integer',
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
