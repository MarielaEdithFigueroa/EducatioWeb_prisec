<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\ProyectarGrupos;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\ProyectarGruposRequest;
use App\Models\AnioLectivo;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ProyectarGruposController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProyectarGruposRequest $request, AnioLectivo $anioLectivo, ProyectarGrupos $proyectar): RedirectResponse
    {
        $destino = AnioLectivo::query()->findOrFail($request->integer('destino_id'));
        $resultado = $proyectar->ejecutar($anioLectivo, $destino);
        $mensaje = "Proyección terminada: {$resultado['creados']} creados, {$resultado['reactivados']} reactivados y {$resultado['omitidos']} omitidos.";
        Inertia::flash('toast', ['type' => 'success', 'message' => $mensaje]);

        return to_route('academico.grupos.index', ['anio_lectivo_id' => $destino->id]);
    }
}
