<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\MarcarAnioLectivoVigente;
use App\Actions\Academico\PersistirEntidadAcademica;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CambiarEstadoAcademicoRequest;
use App\Http\Requests\Academico\GuardarAnioLectivoRequest;
use App\Http\Requests\Academico\MarcarAnioLectivoVigenteRequest;
use App\Models\AnioLectivo;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AnioLectivoController extends Controller
{
    public function store(GuardarAnioLectivoRequest $request, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->crear(new AnioLectivo, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo creado en preparación.']);

        return to_route('academico.estructura.index');
    }

    public function update(GuardarAnioLectivoRequest $request, AnioLectivo $anioLectivo, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->actualizar($anioLectivo, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo actualizado.']);

        return to_route('academico.estructura.index');
    }

    public function desactivar(CambiarEstadoAcademicoRequest $request, AnioLectivo $anioLectivo, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($anioLectivo, false);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo desactivado.']);

        return to_route('academico.estructura.index');
    }

    public function reactivar(CambiarEstadoAcademicoRequest $request, AnioLectivo $anioLectivo, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($anioLectivo, true);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Año lectivo reactivado.']);

        return to_route('academico.estructura.index');
    }

    public function marcarVigente(MarcarAnioLectivoVigenteRequest $request, AnioLectivo $anioLectivo, MarcarAnioLectivoVigente $marcarVigente): RedirectResponse
    {
        $marcarVigente->ejecutar($anioLectivo);
        Inertia::flash('toast', ['type' => 'success', 'message' => "El año {$anioLectivo->anio} quedó vigente."]);

        return to_route('academico.estructura.index');
    }
}
