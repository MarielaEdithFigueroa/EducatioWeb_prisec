<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class AlumnoGrupo extends Model
{
    protected $table = 'alumnos_grupos';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected static function booted(): void
    {
        static::updating(function (AlumnoGrupo $asignacion): void {
            if ($asignacion->isDirty() && (
                $asignacion->getRawOriginal('fecha_hasta') !== null
                || $asignacion->isDirty(['alumno_id', 'grupo_id', 'grupo_descripcion', 'fecha_desde', 'motivo_ingreso'])
                || $asignacion->fecha_hasta === null
            )) {
                throw new LogicException('Las permanencias sólo pueden cerrarse; no se reabren ni reescriben.');
            }
        });
        static::deleting(function (): void {
            throw new LogicException('El historial de grupos no se elimina.');
        });
    }

    protected function casts(): array
    {
        return [
            'alumno_id' => 'integer',
            'grupo_id' => 'integer',
            'fecha_desde' => 'immutable_datetime',
            'fecha_hasta' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Alumno, $this> */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /** @return BelongsTo<Grupo, $this> */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /** @return HasMany<Log, $this> */
    public function auditoria(): HasMany
    {
        return $this->hasMany(Log::class, 'entidad_id')->where('entidad', $this->getTable());
    }

    public static function version(?self $ultima): string
    {
        return $ultima === null ? '0' : $ultima->id.($ultima->fecha_hasta === null ? ':vigente' : ':cerrada');
    }
}
