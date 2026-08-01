<?php

namespace App\Actions\Academico;

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarcarAnioLectivoVigente
{
    use RegistraAuditoria;

    public function ejecutar(AnioLectivo $anioLectivo): AnioLectivo
    {
        return DB::transaction(function () use ($anioLectivo): AnioLectivo {
            $anios = AnioLectivo::query()->lockForUpdate()->get();
            $destino = $anios->firstWhere('id', $anioLectivo->id);

            if (! $destino instanceof AnioLectivo
                || ! $destino->activo
                || $destino->estado === EstadoAnioLectivo::Cerrado) {
                throw ValidationException::withMessages([
                    'anio' => 'Sólo un año activo y en preparación puede marcarse como vigente.',
                ]);
            }

            $vigentesAnteriores = $anios->filter(
                fn (AnioLectivo $anio): bool => $anio->estado === EstadoAnioLectivo::Vigente && $anio->id !== $destino->id,
            );

            if ($vigentesAnteriores->contains(
                fn (AnioLectivo $vigente): bool => $vigente->anio >= $destino->anio,
            )) {
                throw ValidationException::withMessages([
                    'anio' => 'El nuevo año vigente debe ser posterior al vigente actual.',
                ]);
            }

            foreach ($vigentesAnteriores as $anteriorVigente) {
                $anterior = $anteriorVigente->getAttributes();
                $anteriorVigente->estado = EstadoAnioLectivo::Cerrado;
                $anteriorVigente->save();
                $this->auditar('update', $anteriorVigente, $anterior, $anteriorVigente->getAttributes());
            }

            if ($destino->estado !== EstadoAnioLectivo::Vigente) {
                $anterior = $destino->getAttributes();
                $destino->estado = EstadoAnioLectivo::Vigente;
                $destino->save();
                $this->auditar('update', $destino, $anterior, $destino->getAttributes());
            }

            return $destino;
        });
    }
}
