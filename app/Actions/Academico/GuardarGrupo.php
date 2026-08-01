<?php

namespace App\Actions\Academico;

use App\Models\AnioLectivo;
use App\Models\Grupo;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuardarGrupo
{
    use RegistraAuditoria;

    /**
     * @param  array{anio_lectivo_id: int, plan_estudio_id: int, curso_id: int, division_id: int, turno_id: int}  $datos
     * @return array{grupo: Grupo, reactivado: bool}
     */
    public function ejecutar(array $datos): array
    {
        return DB::transaction(function () use ($datos): array {
            AnioLectivo::query()->whereKey($datos['anio_lectivo_id'])->lockForUpdate()->firstOrFail();
            $existente = Grupo::query()->where($datos)->lockForUpdate()->first();

            if ($existente?->activo) {
                throw ValidationException::withMessages([
                    'turno_id' => 'Ya existe un grupo activo con esa combinación.',
                ]);
            }

            if ($existente instanceof Grupo) {
                $anterior = $existente->getAttributes();
                $existente->activo = true;
                $existente->save();
                $this->auditar('reactivate', $existente, $anterior, $existente->getAttributes());

                return ['grupo' => $existente, 'reactivado' => true];
            }

            $grupo = Grupo::query()->create($datos);
            $this->auditar('create', $grupo, null, $grupo->getAttributes());

            return ['grupo' => $grupo, 'reactivado' => false];
        });
    }
}
