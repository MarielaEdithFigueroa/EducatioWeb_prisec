<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\AnioLectivoStoreRequest;
use App\Http\Requests\Academico\AnioLectivoUpdateRequest;
use App\Models\AnioLectivo;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnioLectivoController extends Controller
{
    use RegistraAuditoria;

    public function index(): Response
    {
        $this->authorize('viewAny', AnioLectivo::class);

        return Inertia::render('academico/anios-lectivos/index', [
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(['id', 'activo', 'anio', 'vigente']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AnioLectivo::class);

        return Inertia::render('academico/anios-lectivos/form');
    }

    public function store(AnioLectivoStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', AnioLectivo::class);

        DB::transaction(function () use ($request) {
            $anioLectivo = AnioLectivo::create($request->validated());
            $this->auditar('create', $anioLectivo, null, $anioLectivo->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo creado.']);

        return to_route('academico.anios-lectivos.index');
    }

    public function edit(AnioLectivo $anioLectivo): Response
    {
        $this->authorize('update', $anioLectivo);

        return Inertia::render('academico/anios-lectivos/form', [
            'anioLectivo' => $anioLectivo->only(['id', 'activo', 'anio', 'vigente']),
        ]);
    }

    public function update(AnioLectivoUpdateRequest $request, AnioLectivo $anioLectivo): RedirectResponse
    {
        $this->authorize('update', $anioLectivo);

        DB::transaction(function () use ($request, $anioLectivo) {
            $anterior = $anioLectivo->attributesToArray();
            $anioLectivo->update($request->validated());
            $this->auditar('update', $anioLectivo, $anterior, $anioLectivo->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo actualizado.']);

        return to_route('academico.anios-lectivos.index');
    }

    public function desactivar(AnioLectivo $anioLectivo): RedirectResponse
    {
        $this->authorize('update', $anioLectivo);

        if ($anioLectivo->activo) {
            DB::transaction(function () use ($anioLectivo) {
                $anterior = $anioLectivo->attributesToArray();
                $anioLectivo->update(['activo' => false]);
                $this->auditar('deactivate', $anioLectivo, $anterior, $anioLectivo->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo dado de baja.']);

        return to_route('academico.anios-lectivos.index');
    }

    public function reactivar(AnioLectivo $anioLectivo): RedirectResponse
    {
        $this->authorize('update', $anioLectivo);

        if (! $anioLectivo->activo) {
            DB::transaction(function () use ($anioLectivo) {
                $anterior = $anioLectivo->attributesToArray();
                $anioLectivo->update(['activo' => true]);
                $this->auditar('reactivate', $anioLectivo, $anterior, $anioLectivo->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo reactivado.']);

        return to_route('academico.anios-lectivos.index');
    }
}
