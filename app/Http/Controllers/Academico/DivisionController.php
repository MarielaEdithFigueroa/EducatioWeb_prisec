<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\DivisionStoreRequest;
use App\Http\Requests\Academico\DivisionUpdateRequest;
use App\Models\Division;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DivisionController extends Controller
{
    use RegistraAuditoria;

    public function index(): Response
    {
        $this->authorize('viewAny', Division::class);

        return Inertia::render('academico/divisiones/index', [
            'divisiones' => Division::orderBy('codigo')->get(['id', 'activo', 'codigo', 'descripcion']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Division::class);

        return Inertia::render('academico/divisiones/form');
    }

    public function store(DivisionStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Division::class);

        DB::transaction(function () use ($request) {
            $division = Division::create($request->validated());
            $this->auditar('create', $division, null, $division->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'División creada.']);

        return to_route('academico.divisiones.index');
    }

    public function edit(Division $division): Response
    {
        $this->authorize('update', $division);

        return Inertia::render('academico/divisiones/form', [
            'division' => $division->only(['id', 'activo', 'codigo', 'descripcion']),
        ]);
    }

    public function update(DivisionUpdateRequest $request, Division $division): RedirectResponse
    {
        $this->authorize('update', $division);

        DB::transaction(function () use ($request, $division) {
            $anterior = $division->attributesToArray();
            $division->update($request->validated());
            $this->auditar('update', $division, $anterior, $division->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'División actualizada.']);

        return to_route('academico.divisiones.index');
    }

    public function desactivar(Division $division): RedirectResponse
    {
        $this->authorize('update', $division);

        if ($division->activo) {
            DB::transaction(function () use ($division) {
                $anterior = $division->attributesToArray();
                $division->update(['activo' => false]);
                $this->auditar('deactivate', $division, $anterior, $division->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'División dada de baja.']);

        return to_route('academico.divisiones.index');
    }

    public function reactivar(Division $division): RedirectResponse
    {
        $this->authorize('update', $division);

        if (! $division->activo) {
            DB::transaction(function () use ($division) {
                $anterior = $division->attributesToArray();
                $division->update(['activo' => true]);
                $this->auditar('reactivate', $division, $anterior, $division->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'División reactivada.']);

        return to_route('academico.divisiones.index');
    }
}
