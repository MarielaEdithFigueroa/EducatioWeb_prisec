<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\PersistirEntidadAcademica;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CambiarEstadoAcademicoRequest;
use App\Http\Requests\Academico\GuardarPlanEstudioRequest;
use App\Models\PlanEstudio;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PlanEstudioController extends Controller
{
    public function store(GuardarPlanEstudioRequest $request, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->crear(new PlanEstudio, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Plan de estudio creado.']);

        return to_route('academico.estructura.index');
    }

    public function update(GuardarPlanEstudioRequest $request, PlanEstudio $planEstudio, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->actualizar($planEstudio, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Plan de estudio actualizado.']);

        return to_route('academico.estructura.index');
    }

    public function desactivar(CambiarEstadoAcademicoRequest $request, PlanEstudio $planEstudio, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($planEstudio, false);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Plan de estudio desactivado.']);

        return to_route('academico.estructura.index');
    }

    public function reactivar(CambiarEstadoAcademicoRequest $request, PlanEstudio $planEstudio, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($planEstudio, true);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Plan de estudio reactivado.']);

        return to_route('academico.estructura.index');
    }
}
