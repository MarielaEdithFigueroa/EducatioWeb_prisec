<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\PanelGruposRequest;
use App\Models\Grupo;
use Inertia\Inertia;
use Inertia\Response;

class GrupoPanelController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PanelGruposRequest $request): Response
    {
        $grupos = Grupo::query()->with(['nivel', 'curso', 'division', 'turno'])
            ->orderByDesc('id')->get();

        return Inertia::render('academico/grupos/panel', [
            'grupos' => $grupos->map(fn (Grupo $grupo): array => [
                ...$grupo->only(['id', 'activo', 'ciclo_lectivo']),
                'descripcion' => $grupo->descripcion,
                'nivel' => $grupo->nivel->descripcion,
                'curso_division' => $grupo->curso->descripcion.' '.$grupo->division->descripcion,
                'turno' => $grupo->turno->descripcion,
            ]),
            'cicloInicial' => (int) ($request->validated('ciclo_lectivo') ?? now()->year),
        ]);
    }
}
