<?php

namespace App\Actions\Academico;

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use App\Models\Grupo;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProyectarGrupos
{
    use RegistraAuditoria;

    /** @return array{creados: int, reactivados: int, omitidos: int} */
    public function ejecutar(AnioLectivo $origen, AnioLectivo $destino): array
    {
        return DB::transaction(function () use ($origen, $destino): array {
            $anios = AnioLectivo::query()
                ->whereKey([$origen->id, $destino->id])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $origenBloqueado = $anios->get($origen->id);
            $destinoBloqueado = $anios->get($destino->id);

            if (! $origenBloqueado?->activo
                || ! $destinoBloqueado?->activo
                || $destinoBloqueado->estado !== EstadoAnioLectivo::Preparacion
                || $destinoBloqueado->anio <= $origenBloqueado->anio) {
                throw ValidationException::withMessages([
                    'destino_id' => 'La proyección requiere un origen activo y un año posterior en preparación.',
                ]);
            }

            $resultado = ['creados' => 0, 'reactivados' => 0, 'omitidos' => 0];
            $gruposOrigen = Grupo::query()
                ->activos()
                ->whereBelongsTo($origenBloqueado, 'anioLectivo')
                ->with(['planEstudio:id,nivel_id,activo', 'curso:id,nivel_id,activo', 'division:id,nivel_id,activo', 'turno:id,nivel_id,activo'])
                ->orderBy('id')
                ->get(['plan_estudio_id', 'curso_id', 'division_id', 'turno_id']);

            foreach ($gruposOrigen as $grupoOrigen) {
                $relacionesActivas = $grupoOrigen->planEstudio->activo
                    && $grupoOrigen->curso->activo
                    && $grupoOrigen->division->activo
                    && $grupoOrigen->turno->activo;
                $niveles = collect([
                    $grupoOrigen->planEstudio->nivel_id,
                    $grupoOrigen->curso->nivel_id,
                    $grupoOrigen->division->nivel_id,
                    $grupoOrigen->turno->nivel_id,
                ])->unique();

                if (! $relacionesActivas || $niveles->count() !== 1) {
                    throw ValidationException::withMessages([
                        'anio_lectivo_id' => 'El año de origen contiene grupos con catálogos inactivos o niveles inconsistentes.',
                    ]);
                }

                $datos = [
                    'anio_lectivo_id' => $destinoBloqueado->id,
                    'plan_estudio_id' => $grupoOrigen->plan_estudio_id,
                    'curso_id' => $grupoOrigen->curso_id,
                    'division_id' => $grupoOrigen->division_id,
                    'turno_id' => $grupoOrigen->turno_id,
                ];

                $grupoDestino = Grupo::query()->where($datos)->lockForUpdate()->first();

                if ($grupoDestino?->activo) {
                    $resultado['omitidos']++;

                    continue;
                }

                if ($grupoDestino instanceof Grupo) {
                    $anterior = $grupoDestino->getAttributes();
                    $grupoDestino->activo = true;
                    $grupoDestino->save();
                    $this->auditar('reactivate', $grupoDestino, $anterior, $grupoDestino->getAttributes());
                    $resultado['reactivados']++;

                    continue;
                }

                $grupoDestino = Grupo::query()->create($datos);
                $this->auditar('create', $grupoDestino, null, $grupoDestino->getAttributes());
                $resultado['creados']++;
            }

            return $resultado;
        });
    }
}
