<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\NivelStoreRequest;
use App\Http\Requests\Academico\NivelUpdateRequest;
use App\Models\Nivel;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class NivelController extends Controller
{
    use RegistraAuditoria;

    public function index(): Response
    {
        $this->authorize('viewAny', Nivel::class);

        return Inertia::render('academico/niveles/index', [
            'niveles' => Nivel::orderBy('codigo')->get(['id', 'activo', 'codigo', 'descripcion']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Nivel::class);

        return Inertia::render('academico/niveles/form');
    }

    public function store(NivelStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Nivel::class);

        DB::transaction(function () use ($request) {
            $nivel = Nivel::create($request->validated());
            $this->auditar('create', $nivel, null, $nivel->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Nivel creado.']);

        return to_route('academico.niveles.index');
    }

    public function edit(Nivel $nivel): Response
    {
        $this->authorize('update', $nivel);

        return Inertia::render('academico/niveles/form', [
            'nivel' => $nivel->only(['id', 'activo', 'codigo', 'descripcion']),
        ]);
    }

    public function update(NivelUpdateRequest $request, Nivel $nivel): RedirectResponse
    {
        $this->authorize('update', $nivel);

        DB::transaction(function () use ($request, $nivel) {
            $anterior = $nivel->attributesToArray();
            $nivel->update($request->validated());
            $this->auditar('update', $nivel, $anterior, $nivel->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Nivel actualizado.']);

        return to_route('academico.niveles.index');
    }

    public function desactivar(Nivel $nivel): RedirectResponse
    {
        $this->authorize('update', $nivel);

        if ($nivel->activo) {
            DB::transaction(function () use ($nivel) {
                $anterior = $nivel->attributesToArray();
                $nivel->update(['activo' => false]);
                $this->auditar('deactivate', $nivel, $anterior, $nivel->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Nivel dado de baja.']);

        return to_route('academico.niveles.index');
    }

    public function reactivar(Nivel $nivel): RedirectResponse
    {
        $this->authorize('update', $nivel);

        if (! $nivel->activo) {
            DB::transaction(function () use ($nivel) {
                $anterior = $nivel->attributesToArray();
                $nivel->update(['activo' => true]);
                $this->auditar('reactivate', $nivel, $anterior, $nivel->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Nivel reactivado.']);

        return to_route('academico.niveles.index');
    }
}
