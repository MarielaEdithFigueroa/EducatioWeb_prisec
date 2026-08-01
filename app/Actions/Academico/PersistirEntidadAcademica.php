<?php

namespace App\Actions\Academico;

use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PersistirEntidadAcademica
{
    use RegistraAuditoria;

    /** @param array<string, mixed> $datos */
    public function crear(Model $modelo, array $datos): Model
    {
        return DB::transaction(function () use ($modelo, $datos): Model {
            $modelo->fill($datos);
            $modelo->save();
            $this->auditar('create', $modelo, null, $modelo->getAttributes());

            return $modelo;
        });
    }

    /** @param array<string, mixed> $datos */
    public function actualizar(Model $modelo, array $datos): Model
    {
        return DB::transaction(function () use ($modelo, $datos): Model {
            $anterior = $modelo->getAttributes();
            $modelo->fill($datos);
            $modelo->save();
            $this->auditar('update', $modelo, $anterior, $modelo->getAttributes());

            return $modelo;
        });
    }

    public function cambiarActivo(Model $modelo, bool $activo): Model
    {
        return DB::transaction(function () use ($modelo, $activo): Model {
            if ((bool) $modelo->getAttribute('activo') === $activo) {
                return $modelo;
            }

            $anterior = $modelo->getAttributes();
            $modelo->setAttribute('activo', $activo);
            $modelo->save();
            $this->auditar($activo ? 'reactivate' : 'deactivate', $modelo, $anterior, $modelo->getAttributes());

            return $modelo;
        });
    }
}
