<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\TurnoRequest;
use App\Models\Turno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TurnoController extends CatalogoController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Turno::class);

        return Inertia::render('academico/catalogos/index', [
            'catalogo' => [
                'clave' => 'turnos',
                'titulo' => 'Turnos',
                'singular' => 'turno',
                'descripcion' => 'Turnos en los que se organiza la actividad escolar.',
                'usa_codigo' => false,
                'usa_nivel' => false,
                'usa_orden' => true,
            ],
            'registros' => Turno::query()
                ->select(['id', 'activo', 'descripcion', 'orden'])
                ->orderByDesc('activo')
                ->orderBy('orden')
                ->orderBy('descripcion')
                ->get(),
            'niveles' => [],
        ]);
    }

    public function store(TurnoRequest $request): RedirectResponse
    {
        $this->crear(new Turno, $request->validated());
        $this->toast('Turno creado.');

        return back();
    }

    public function update(TurnoRequest $request, Turno $turno): RedirectResponse
    {
        $this->actualizar($turno, $request->validated());
        $this->toast('Turno actualizado.');

        return back();
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, Turno $turno): RedirectResponse
    {
        $this->cambiarEstado($turno, false);
        $this->toast('Turno desactivado.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, Turno $turno): RedirectResponse
    {
        $this->cambiarEstado($turno, true);
        $this->toast('Turno reactivado.');

        return back();
    }
}
