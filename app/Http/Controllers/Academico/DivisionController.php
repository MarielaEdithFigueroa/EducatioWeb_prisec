<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\DivisionRequest;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DivisionController extends CatalogoController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Division::class);

        return Inertia::render('academico/catalogos/index', [
            'catalogo' => [
                'clave' => 'divisiones',
                'titulo' => 'Divisiones',
                'singular' => 'división',
                'descripcion' => 'Divisiones utilizadas para conformar los grupos.',
                'usa_codigo' => false,
                'usa_nivel' => false,
                'usa_orden' => true,
            ],
            'registros' => Division::query()
                ->select(['id', 'activo', 'descripcion', 'orden'])
                ->orderByDesc('activo')
                ->orderBy('orden')
                ->orderBy('descripcion')
                ->get(),
            'niveles' => [],
        ]);
    }

    public function store(DivisionRequest $request): RedirectResponse
    {
        $this->crear(new Division, $request->validated());
        $this->toast('División creada.');

        return back();
    }

    public function update(DivisionRequest $request, Division $division): RedirectResponse
    {
        $this->actualizar($division, $request->validated());
        $this->toast('División actualizada.');

        return back();
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, Division $division): RedirectResponse
    {
        $this->cambiarEstado($division, false);
        $this->toast('División desactivada.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, Division $division): RedirectResponse
    {
        $this->cambiarEstado($division, true);
        $this->toast('División reactivada.');

        return back();
    }
}
