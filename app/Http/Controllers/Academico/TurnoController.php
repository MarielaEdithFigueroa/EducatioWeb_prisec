<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\TurnoStoreRequest;
use App\Http\Requests\Academico\TurnoUpdateRequest;
use App\Models\Turno;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TurnoController extends Controller
{
    use RegistraAuditoria;

    public function index(): Response
    {
        $this->authorize('viewAny', Turno::class);

        return Inertia::render('academico/turnos/index', [
            'turnos' => Turno::orderBy('codigo')->get(['id', 'activo', 'codigo', 'descripcion']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Turno::class);

        return Inertia::render('academico/turnos/form');
    }

    public function store(TurnoStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Turno::class);

        DB::transaction(function () use ($request) {
            $turno = Turno::create($request->validated());
            $this->auditar('create', $turno, null, $turno->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno creado.']);

        return to_route('academico.turnos.index');
    }

    public function edit(Turno $turno): Response
    {
        $this->authorize('update', $turno);

        return Inertia::render('academico/turnos/form', [
            'turno' => $turno->only(['id', 'activo', 'codigo', 'descripcion']),
        ]);
    }

    public function update(TurnoUpdateRequest $request, Turno $turno): RedirectResponse
    {
        $this->authorize('update', $turno);

        DB::transaction(function () use ($request, $turno) {
            $anterior = $turno->attributesToArray();
            $turno->update($request->validated());
            $this->auditar('update', $turno, $anterior, $turno->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno actualizado.']);

        return to_route('academico.turnos.index');
    }

    public function desactivar(Turno $turno): RedirectResponse
    {
        $this->authorize('update', $turno);

        if ($turno->activo) {
            DB::transaction(function () use ($turno) {
                $anterior = $turno->attributesToArray();
                $turno->update(['activo' => false]);
                $this->auditar('deactivate', $turno, $anterior, $turno->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno dado de baja.']);

        return to_route('academico.turnos.index');
    }

    public function reactivar(Turno $turno): RedirectResponse
    {
        $this->authorize('update', $turno);

        if (! $turno->activo) {
            DB::transaction(function () use ($turno) {
                $anterior = $turno->attributesToArray();
                $turno->update(['activo' => true]);
                $this->auditar('reactivate', $turno, $anterior, $turno->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno reactivado.']);

        return to_route('academico.turnos.index');
    }
}
