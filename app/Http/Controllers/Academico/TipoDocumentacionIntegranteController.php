<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\TipoDocumentacionIntegranteRequest;
use App\Models\TipoDocumentacionIntegrante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TipoDocumentacionIntegranteController extends CatalogoController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', TipoDocumentacionIntegrante::class);

        return Inertia::render('academico/catalogos/index', [
            'catalogo' => [
                'clave' => 'tipos_documentacion_integrante',
                'titulo' => 'Tipos de documentación del equipo',
                'singular' => 'tipo de documentación',
                'descripcion' => 'Documentos que integran el checklist del legajo de cada integrante.',
                'usa_codigo' => false,
                'usa_nivel' => false,
                'usa_orden' => false,
                'longitud_descripcion' => 150,
            ],
            'registros' => TipoDocumentacionIntegrante::query()
                ->select(['id', 'activo', 'descripcion'])
                ->orderByDesc('activo')
                ->orderBy('descripcion')
                ->get(),
            'niveles' => [],
        ]);
    }

    public function store(TipoDocumentacionIntegranteRequest $request): RedirectResponse
    {
        $this->crear(new TipoDocumentacionIntegrante, $request->validated());
        $this->toast('Tipo de documentación creado.');

        return back();
    }

    public function update(TipoDocumentacionIntegranteRequest $request, TipoDocumentacionIntegrante $tipoDocumentacionIntegrante): RedirectResponse
    {
        $this->actualizar($tipoDocumentacionIntegrante, $request->validated());
        $this->toast('Tipo de documentación actualizado.');

        return back();
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, TipoDocumentacionIntegrante $tipoDocumentacionIntegrante): RedirectResponse
    {
        $this->cambiarEstado($tipoDocumentacionIntegrante, false);
        $this->toast('Tipo de documentación desactivado.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, TipoDocumentacionIntegrante $tipoDocumentacionIntegrante): RedirectResponse
    {
        $this->cambiarEstado($tipoDocumentacionIntegrante, true);
        $this->toast('Tipo de documentación reactivado.');

        return back();
    }
}
