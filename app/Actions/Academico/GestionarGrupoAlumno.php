<?php

namespace App\Actions\Academico;

use App\Models\Alumno;
use App\Models\AlumnoGrupo;
use App\Models\Grupo;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class GestionarGrupoAlumno
{
    use RegistraAuditoria;

    /**
     * @param  array<int, array{alumno_id: int, version_historial: string}>  $seleccion
     * @return array{asignados: int, omitidos: int}
     */
    public function asignarMasivamente(Grupo $grupo, array $seleccion, string $motivo): array
    {
        return DB::transaction(function () use ($grupo, $seleccion, $motivo): array {
            $versiones = collect($seleccion)->keyBy('alumno_id');
            $alumnos = Alumno::query()->whereIn('id', $versiones->keys())->orderBy('id')->lockForUpdate()->get();

            if ($alumnos->count() !== count($seleccion)) {
                throw ValidationException::withMessages(['alumnos' => 'La selección contiene alumnos repetidos o que ya no existen. Recargá la lista.']);
            }

            $destino = Grupo::query()->lockForUpdate()->findOrFail($grupo->id);
            if (! $destino->activo) {
                throw ValidationException::withMessages(['alumnos' => 'El grupo de destino está inactivo.']);
            }

            $resultado = ['asignados' => 0, 'omitidos' => 0];
            foreach ($alumnos as $alumno) {
                Gate::authorize('update', $alumno);
                $sinCambios = $alumno->grupo_id === $destino->id;
                try {
                    $this->cambiar($alumno, $destino->id, $versiones[$alumno->id]['version_historial'], $motivo);
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages([
                        'alumnos' => $alumno->apellido.', '.$alumno->nombre.' (legajo '.$alumno->legajo.'): '.collect($exception->errors())->flatten()->first().' No se guardó ningún movimiento del lote.',
                    ]);
                }
                $resultado[$sinCambios ? 'omitidos' : 'asignados']++;
            }

            return $resultado;
        }, attempts: 3);
    }

    public function cambiar(Alumno $alumno, ?int $grupoId, string $version, string $motivo): void
    {
        DB::transaction(function () use ($alumno, $grupoId, $version, $motivo): void {
            $bloqueado = Alumno::query()->lockForUpdate()->findOrFail($alumno->id);
            $ultima = $bloqueado->historialGrupos()->orderByDesc('id')->lockForUpdate()->first();

            if (AlumnoGrupo::version($ultima) !== $version) {
                throw ValidationException::withMessages(['grupo_id' => 'La asignación cambió desde que abriste la ficha. Recargá antes de continuar.']);
            }

            if (! $bloqueado->activo) {
                throw ValidationException::withMessages(['grupo_id' => 'Reactivá al alumno antes de asignarle un grupo.']);
            }

            $this->guardar($bloqueado, $grupoId, $motivo);
        }, attempts: 3);
    }

    public function cerrarPorBaja(Alumno $alumno): void
    {
        DB::transaction(function () use ($alumno): void {
            $bloqueado = Alumno::query()->lockForUpdate()->findOrFail($alumno->id);
            $this->guardar($bloqueado, null, 'Baja del alumno');
            $alumno->refresh();
        });
    }

    private function guardar(Alumno $alumno, ?int $grupoId, string $motivo): void
    {
        $actual = $alumno->asignacionActual()->lockForUpdate()->first();

        if ($alumno->grupo_id !== $actual?->grupo_id) {
            throw ValidationException::withMessages(['grupo_id' => 'El grupo del alumno no coincide con su historial. Es necesario revisar la inconsistencia antes de continuar.']);
        }

        if ($alumno->grupo_id === $grupoId) {
            return;
        }

        $grupo = $grupoId === null ? null : Grupo::query()->lockForUpdate()->find($grupoId);

        if ($grupoId !== null && ($grupo === null || ! $grupo->activo)) {
            throw ValidationException::withMessages(['grupo_id' => 'Seleccioná un grupo activo.']);
        }

        $instante = now()->toImmutable();
        $ultima = $alumno->historialGrupos()->orderByDesc('id')->first();
        $ultimoInstante = $ultima?->fecha_hasta ?? $ultima?->fecha_desde;

        if ($ultimoInstante !== null && $instante->lessThan($ultimoInstante)) {
            throw ValidationException::withMessages(['grupo_id' => 'La fecha del sistema es anterior al último movimiento del alumno.']);
        }

        if ($actual !== null) {
            $anterior = $actual->attributesToArray();
            $actual->forceFill(['fecha_hasta' => $instante, 'motivo_egreso' => $motivo])->save();
            $actual->refresh();
            $this->auditar('update', $actual, $anterior, $actual->attributesToArray());
        }

        if ($grupo !== null) {
            $grupo->load(['nivel', 'curso', 'division', 'turno']);
            $nueva = new AlumnoGrupo;
            $nueva->forceFill([
                'alumno_id' => $alumno->id,
                'grupo_id' => $grupo->id,
                'grupo_descripcion' => $grupo->descripcion,
                'fecha_desde' => $instante,
                'motivo_ingreso' => $motivo,
            ])->save();
            $nueva->refresh();
            $this->auditar('create', $nueva, null, $nueva->attributesToArray());
        }

        $anterior = $alumno->attributesToArray();
        $alumno->forceFill(['grupo_id' => $grupoId])->save();
        $alumno->refresh();
        $this->auditar('update', $alumno, $anterior, $alumno->attributesToArray());
    }
}
