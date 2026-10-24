<?php

namespace App\Actions\Academico;

use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class GuardarRelacionesIntegrante
{
    use RegistraAuditoria;

    /** @param list<array<string, mixed>> $filas
     * @param  list<int|string>  $bajas
     */
    public function ejecutar(HasMany $relacion, array $filas, array $bajas, string $campo): void
    {
        $existentes = $relacion->where('activo', true)->get()->keyBy('id');
        foreach ($filas as $indice => $datos) {
            $id = $datos['id'] ?? null;
            $registro = $id ? $existentes->get($id) : $relacion->make();
            if ($registro === null || ($id && in_array($id, $bajas))) {
                throw ValidationException::withMessages(["$campo.$indice.id" => 'La fila ya no está disponible. Recargá la ficha.']);
            }
            unset($datos['id']);
            $datos = array_filter($datos, fn (string $clave): bool => ! str_ends_with($clave, '_es_fijo'), ARRAY_FILTER_USE_KEY);
            $nuevo = ! $registro->exists;
            $anterior = $nuevo ? null : $registro->attributesToArray();
            $registro->fill($datos);
            if ($nuevo || $registro->isDirty()) {
                $registro->save();
                $registro->refresh();
                $this->auditar($nuevo ? 'create' : 'update', $registro, $anterior, $registro->attributesToArray());
            }
        }
        foreach ($bajas as $indice => $id) {
            $registro = $existentes->get($id);
            if ($registro === null) {
                throw ValidationException::withMessages(["{$campo}_bajas.$indice" => 'La fila ya no está disponible. Recargá la ficha.']);
            }
            $anterior = $registro->attributesToArray();
            $registro->update(['activo' => false]);
            $this->auditar('deactivate', $registro, $anterior, $registro->attributesToArray());
        }
    }
}
