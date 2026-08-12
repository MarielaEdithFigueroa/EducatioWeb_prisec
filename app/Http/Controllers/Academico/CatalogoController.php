<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Models\Nivel;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

abstract class CatalogoController extends Controller
{
    use RegistraAuditoria;

    /** @param array<string, mixed> $datos */
    protected function crear(Model $modelo, array $datos): void
    {
        DB::transaction(function () use ($modelo, $datos): void {
            $modelo->fill($datos)->save();
            $modelo->refresh();

            $this->auditar('create', $modelo, null, $modelo->attributesToArray());
        });
    }

    /** @param array<string, mixed> $datos */
    protected function actualizar(Model $modelo, array $datos): void
    {
        DB::transaction(function () use ($modelo, $datos): void {
            $anterior = $modelo->attributesToArray();
            $modelo->fill($datos);

            if (! $modelo->isDirty()) {
                return;
            }

            $modelo->save();
            $modelo->refresh();

            $this->auditar('update', $modelo, $anterior, $modelo->attributesToArray());
        });
    }

    protected function cambiarEstado(Model $modelo, bool $activo): void
    {
        DB::transaction(function () use ($modelo, $activo): void {
            if ((bool) $modelo->getAttribute('activo') === $activo) {
                return;
            }

            $anterior = $modelo->attributesToArray();
            $modelo->setAttribute('activo', $activo);
            $modelo->save();
            $modelo->refresh();

            $this->auditar(
                $activo ? 'reactivate' : 'deactivate',
                $modelo,
                $anterior,
                $modelo->attributesToArray(),
            );
        });
    }

    /** @return array<int, array{id: int, codigo: string, descripcion: string, activo: bool}> */
    protected function niveles(): array
    {
        return Nivel::query()
            ->select(['id', 'codigo', 'descripcion', 'activo'])
            ->orderByDesc('activo')
            ->orderBy('descripcion')
            ->get()
            ->toArray();
    }

    protected function toast(string $mensaje): void
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $mensaje]);
    }
}
