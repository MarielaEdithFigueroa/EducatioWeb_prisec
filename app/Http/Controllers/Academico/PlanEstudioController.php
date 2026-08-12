<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\PlanEstudioRequest;
use App\Models\PlanEstudio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PlanEstudioController extends CatalogoController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', PlanEstudio::class);

        return Inertia::render('academico/catalogos/index', [
            'catalogo' => [
                'clave' => 'planes_estudio',
                'titulo' => 'Planes de estudio',
                'singular' => 'plan de estudios',
                'descripcion' => 'Planes de estudio asociados a cada nivel educativo.',
                'usa_codigo' => false,
                'usa_nivel' => true,
                'usa_orden' => true,
            ],
            'registros' => PlanEstudio::query()
                ->select(['id', 'activo', 'descripcion', 'nivel_id', 'orden'])
                ->with('nivel:id,activo,codigo,descripcion')
                ->orderByDesc('activo')
                ->orderBy('nivel_id')
                ->orderBy('orden')
                ->orderBy('descripcion')
                ->get(),
            'niveles' => $this->niveles(),
        ]);
    }

    public function store(PlanEstudioRequest $request): RedirectResponse
    {
        $this->crear(new PlanEstudio, $request->validated());
        $this->toast('Plan de estudios creado.');

        return back();
    }

    public function update(PlanEstudioRequest $request, PlanEstudio $planEstudio): RedirectResponse
    {
        $this->actualizar($planEstudio, $request->validated());
        $this->toast('Plan de estudios actualizado.');

        return back();
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, PlanEstudio $planEstudio): RedirectResponse
    {
        $this->cambiarEstado($planEstudio, false);
        $this->toast('Plan de estudios desactivado.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, PlanEstudio $planEstudio): RedirectResponse
    {
        $this->cambiarEstado($planEstudio, true);
        $this->toast('Plan de estudios reactivado.');

        return back();
    }
}
