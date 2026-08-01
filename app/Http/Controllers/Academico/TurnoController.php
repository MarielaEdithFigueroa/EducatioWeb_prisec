<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\PersistirEntidadAcademica;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CambiarEstadoAcademicoRequest;
use App\Http\Requests\Academico\GuardarTurnoRequest;
use App\Models\Turno;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TurnoController extends Controller
{
    public function store(GuardarTurnoRequest $request, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->crear(new Turno, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno creado.']);

        return to_route('academico.estructura.index');
    }

    public function update(GuardarTurnoRequest $request, Turno $turno, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->actualizar($turno, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno actualizado.']);

        return to_route('academico.estructura.index');
    }

    public function desactivar(CambiarEstadoAcademicoRequest $request, Turno $turno, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($turno, false);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno desactivado.']);

        return to_route('academico.estructura.index');
    }

    public function reactivar(CambiarEstadoAcademicoRequest $request, Turno $turno, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($turno, true);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turno reactivado.']);

        return to_route('academico.estructura.index');
    }
}
