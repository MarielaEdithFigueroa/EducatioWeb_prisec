<?php

namespace App\Http\Controllers\Academico;

use App\Actions\GuardarAdjunto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\ActualizarFotoAulaRequest;
use App\Http\Requests\Academico\CambiarEstadoFotoAulaRequest;
use App\Http\Requests\Academico\GuardarFotoAulaRequest;
use App\Models\Adjunto;
use App\Models\Aula;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FotoAulaController extends Controller
{
    use RegistraAuditoria;

    public function store(GuardarFotoAulaRequest $request, Aula $aula, GuardarAdjunto $guardar): RedirectResponse
    {
        $guardar->ejecutar($aula->adjuntos(), $request->file('archivo'), $request->validated('observaciones'));

        return $this->volver($aula, 'Foto adjuntada.');
    }

    public function descargar(Aula $aula, Adjunto $adjunto): StreamedResponse
    {
        Gate::authorize('view', $aula);
        Gate::authorize('view', $adjunto);

        abort_unless($adjunto->activo, 404);
        abort_unless(Storage::disk($adjunto->disco)->exists($adjunto->ruta), 404);

        return Storage::disk($adjunto->disco)->download($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function miniatura(Aula $aula, Adjunto $adjunto): StreamedResponse
    {
        Gate::authorize('view', $aula);
        Gate::authorize('view', $adjunto);

        abort_unless($adjunto->activo, 404);
        $disco = Storage::disk($adjunto->disco);
        abort_unless($disco->exists($adjunto->ruta), 404);
        $mime = $disco->mimeType($adjunto->ruta);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return $disco->response($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function update(ActualizarFotoAulaRequest $request, Aula $aula, Adjunto $adjunto): RedirectResponse
    {
        DB::transaction(function () use ($request, $adjunto): void {
            $adjunto = Adjunto::query()->lockForUpdate()->findOrFail($adjunto->id);
            abort_unless($adjunto->activo, 404);
            $anterior = $adjunto->attributesToArray();
            $adjunto->fill($request->validated());

            if (! $adjunto->isDirty()) {
                return;
            }

            $adjunto->save();
            $this->auditar('update', $adjunto, $anterior, $adjunto->attributesToArray());
        });

        return $this->volver($aula, 'Observaciones actualizadas.');
    }

    public function desactivar(CambiarEstadoFotoAulaRequest $request, Aula $aula, Adjunto $adjunto): RedirectResponse
    {
        $this->cambiarEstado($adjunto, false);

        return $this->volver($aula, 'Foto desactivada.');
    }

    public function reactivar(CambiarEstadoFotoAulaRequest $request, Aula $aula, Adjunto $adjunto): RedirectResponse
    {
        $this->cambiarEstado($adjunto, true);

        return $this->volver($aula, 'Foto reactivada.');
    }

    private function cambiarEstado(Adjunto $adjunto, bool $activo): void
    {
        DB::transaction(function () use ($adjunto, $activo): void {
            $adjunto = Adjunto::query()->lockForUpdate()->findOrFail($adjunto->id);

            if ($adjunto->activo === $activo) {
                return;
            }

            $anterior = $adjunto->attributesToArray();
            $adjunto->update(['activo' => $activo]);
            $this->auditar($activo ? 'reactivate' : 'deactivate', $adjunto, $anterior, $adjunto->attributesToArray());
        });
    }

    private function volver(Aula $aula, string $mensaje): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $mensaje]);

        return to_route('academico.aulas.show', $aula);
    }
}
