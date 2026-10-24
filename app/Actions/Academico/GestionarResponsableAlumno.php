<?php

namespace App\Actions\Academico;

use App\Models\Alumno;
use App\Models\AlumnoResponsable;
use App\Models\Responsable;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Support\Facades\DB;

class GestionarResponsableAlumno
{
    use RegistraAuditoria;

    /** @param array<string, mixed> $datosVinculo */
    public function vincular(Alumno $alumno, Responsable $responsable, array $datosVinculo): AlumnoResponsable
    {
        return DB::transaction(function () use ($alumno, $responsable, $datosVinculo): AlumnoResponsable {
            $this->reactivarResponsable($responsable);

            return $this->guardarVinculo($alumno, $responsable, $datosVinculo);
        });
    }

    /**
     * @param  array<string, mixed>  $datosResponsable
     * @param  array<string, mixed>  $datosVinculo
     */
    public function crearYVincular(
        Alumno $alumno,
        array $datosResponsable,
        array $datosVinculo,
    ): AlumnoResponsable {
        return DB::transaction(function () use ($alumno, $datosResponsable, $datosVinculo): AlumnoResponsable {
            $responsable = Responsable::query()->create($datosResponsable);
            $responsable->refresh();
            $this->auditar('create', $responsable, null, $responsable->attributesToArray());

            return $this->guardarVinculo($alumno, $responsable, $datosVinculo);
        });
    }

    /** @param array<string, mixed> $datosVinculo */
    private function guardarVinculo(
        Alumno $alumno,
        Responsable $responsable,
        array $datosVinculo,
    ): AlumnoResponsable {
        $vinculo = AlumnoResponsable::query()
            ->where('alumno_id', $alumno->id)
            ->where('responsable_id', $responsable->id)
            ->lockForUpdate()
            ->first();

        if ($vinculo === null) {
            $ordenContacto = ((int) AlumnoResponsable::query()
                ->where('alumno_id', $alumno->id)
                ->where('activo', true)
                ->lockForUpdate()
                ->max('orden_contacto')) + 1;

            $vinculo = AlumnoResponsable::query()->create([
                ...$datosVinculo,
                'activo' => true,
                'alumno_id' => $alumno->id,
                'responsable_id' => $responsable->id,
                'orden_contacto' => $ordenContacto,
            ]);
            $vinculo->refresh();
            $this->auditar('create', $vinculo, null, $vinculo->attributesToArray());

            return $vinculo;
        }

        $anterior = $vinculo->attributesToArray();
        $accion = $vinculo->activo ? 'update' : 'reactivate';
        $vinculo->fill([
            ...$datosVinculo,
            'activo' => true,
        ])->save();
        $vinculo->refresh();

        if ($anterior !== $vinculo->attributesToArray()) {
            $this->auditar($accion, $vinculo, $anterior, $vinculo->attributesToArray());
        }

        return $vinculo;
    }

    private function reactivarResponsable(Responsable $responsable): void
    {
        if ($responsable->activo) {
            return;
        }

        $anterior = $responsable->attributesToArray();
        $responsable->fill(['activo' => true])->save();
        $responsable->refresh();
        $this->auditar('reactivate', $responsable, $anterior, $responsable->attributesToArray());
    }
}
