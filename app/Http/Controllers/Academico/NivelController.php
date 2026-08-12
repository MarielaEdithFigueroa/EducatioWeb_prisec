<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\NivelRequest;
use App\Models\Nivel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NivelController extends CatalogoController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Nivel::class);

        return Inertia::render('academico/catalogos/index', [
            'catalogo' => [
                'clave' => 'niveles',
                'titulo' => 'Niveles',
                'singular' => 'nivel',
                'descripcion' => 'Niveles educativos disponibles en la institución.',
                'usa_codigo' => true,
                'usa_nivel' => false,
                'usa_orden' => false,
            ],
            'registros' => Nivel::query()
                ->select(['id', 'activo', 'codigo', 'descripcion'])
                ->orderByDesc('activo')
                ->orderBy('descripcion')
                ->get(),
            'niveles' => [],
        ]);
    }

    public function store(NivelRequest $request): RedirectResponse
    {
        $this->crear(new Nivel, $request->validated());
        $this->toast('Nivel creado.');

        return back();
    }

    public function update(NivelRequest $request, Nivel $nivel): RedirectResponse
    {
        $this->actualizar($nivel, $request->validated());
        $this->toast('Nivel actualizado.');

        return back();
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, Nivel $nivel): RedirectResponse
    {
        $this->cambiarEstado($nivel, false);
        $this->toast('Nivel desactivado.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, Nivel $nivel): RedirectResponse
    {
        $this->cambiarEstado($nivel, true);
        $this->toast('Nivel reactivado.');

        return back();
    }
}
