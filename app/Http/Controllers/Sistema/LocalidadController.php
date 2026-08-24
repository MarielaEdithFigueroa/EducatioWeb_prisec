<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sistema\CambiarEstadoLocalidadRequest;
use App\Http\Requests\Sistema\LocalidadRequest;
use App\Models\Ciudad;
use App\Models\Provincia;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LocalidadController extends Controller
{
    use RegistraAuditoria;

    public function index(): Response
    {
        Gate::authorize('viewAny', Ciudad::class);

        return Inertia::render('sistema/localidades/index', [
            'localidades' => Ciudad::query()
                ->select(['id', 'activo', 'provincia_id', 'nombre', 'codigo_postal'])
                ->with('provincia:id,activo,codigo,nombre')
                ->orderByDesc('activo')
                ->orderBy('provincia_id')
                ->orderBy('nombre')
                ->get(),
            'provincias' => Provincia::query()
                ->select(['id', 'activo', 'codigo', 'nombre'])
                ->orderByDesc('activo')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function store(LocalidadRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $localidad = Ciudad::query()->create($request->validated());
            $localidad->refresh();

            $this->auditar('create', $localidad, null, $localidad->attributesToArray());
        });

        $this->toast('Localidad creada.');

        return back();
    }

    public function update(LocalidadRequest $request, Ciudad $localidad): RedirectResponse
    {
        DB::transaction(function () use ($request, $localidad): void {
            $anterior = $localidad->attributesToArray();
            $localidad->fill($request->validated());

            if (! $localidad->isDirty()) {
                return;
            }

            $localidad->save();
            $localidad->refresh();

            $this->auditar('update', $localidad, $anterior, $localidad->attributesToArray());
        });

        $this->toast('Localidad actualizada.');

        return back();
    }

    public function desactivar(CambiarEstadoLocalidadRequest $request, Ciudad $localidad): RedirectResponse
    {
        $this->cambiarEstado($localidad, false);
        $this->toast('Localidad desactivada.');

        return back();
    }

    public function reactivar(CambiarEstadoLocalidadRequest $request, Ciudad $localidad): RedirectResponse
    {
        $this->cambiarEstado($localidad, true);
        $this->toast('Localidad reactivada.');

        return back();
    }

    private function cambiarEstado(Ciudad $localidad, bool $activo): void
    {
        DB::transaction(function () use ($localidad, $activo): void {
            if ($localidad->activo === $activo) {
                return;
            }

            $anterior = $localidad->attributesToArray();
            $localidad->activo = $activo;
            $localidad->save();
            $localidad->refresh();

            $this->auditar(
                $activo ? 'reactivate' : 'deactivate',
                $localidad,
                $anterior,
                $localidad->attributesToArray(),
            );
        });
    }

    private function toast(string $mensaje): void
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $mensaje]);
    }
}
