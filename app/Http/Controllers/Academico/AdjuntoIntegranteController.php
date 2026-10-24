<?php

namespace App\Http\Controllers\Academico;

use App\Actions\GuardarAdjunto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\AdjuntoIntegranteRequest;
use App\Models\Adjunto;
use App\Models\Integrante;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdjuntoIntegranteController extends Controller
{
    use RegistraAuditoria;

    public function store(AdjuntoIntegranteRequest $request, Integrante $integrante, GuardarAdjunto $guardar): RedirectResponse
    {
        $adjunto = null;
        try {
            DB::transaction(function () use ($request, $integrante, $guardar, &$adjunto): void {
                $integrante = $this->integranteActivo($integrante);
                $adjunto = $guardar->ejecutar($integrante->adjuntos(), $request->file('archivo'), $request->validated('observaciones'));
            });
        } catch (Throwable $error) {
            if ($adjunto !== null) {
                try {
                    Storage::disk($adjunto->disco)->delete($adjunto->ruta);
                } catch (Throwable $limpieza) {
                    report($limpieza);
                }
            }
            throw $error;
        }

        return $this->volver('Documento adjuntado.');
    }

    public function descargar(Integrante $integrante, Adjunto $adjunto): StreamedResponse
    {
        Gate::authorize('view', $integrante);
        Gate::authorize('view', $adjunto);

        abort_unless($adjunto->activo, 404);
        abort_unless(Storage::disk($adjunto->disco)->exists($adjunto->ruta), 404);

        return Storage::disk($adjunto->disco)->download($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function miniatura(Integrante $integrante, Adjunto $adjunto): StreamedResponse
    {
        Gate::authorize('view', $integrante);
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

    public function update(AdjuntoIntegranteRequest $request, Integrante $integrante, Adjunto $adjunto): RedirectResponse
    {
        DB::transaction(function () use ($request, $integrante, $adjunto): void {
            $this->integranteActivo($integrante);
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

        return $this->volver('Observaciones actualizadas.');
    }

    public function desactivar(AdjuntoIntegranteRequest $request, Integrante $integrante, Adjunto $adjunto): RedirectResponse
    {
        $this->cambiarEstado($integrante, $adjunto, false);

        return $this->volver('Documento desactivado.');
    }

    public function reactivar(AdjuntoIntegranteRequest $request, Integrante $integrante, Adjunto $adjunto): RedirectResponse
    {
        $this->cambiarEstado($integrante, $adjunto, true);

        return $this->volver('Documento reactivado.');
    }

    private function cambiarEstado(Integrante $integrante, Adjunto $adjunto, bool $activo): void
    {
        DB::transaction(function () use ($integrante, $adjunto, $activo): void {
            $this->integranteActivo($integrante);
            $adjunto = Adjunto::query()->lockForUpdate()->findOrFail($adjunto->id);

            if ($adjunto->activo === $activo) {
                return;
            }

            $anterior = $adjunto->attributesToArray();
            $adjunto->update(['activo' => $activo]);
            $this->auditar($activo ? 'reactivate' : 'deactivate', $adjunto, $anterior, $adjunto->attributesToArray());
        });
    }

    private function integranteActivo(Integrante $integrante): Integrante
    {
        $integrante = Integrante::query()->lockForUpdate()->findOrFail($integrante->id);
        if (! $integrante->activo) {
            throw ValidationException::withMessages(['archivo' => 'El integrante está dado de baja. Su documentación es de solo consulta.']);
        }

        return $integrante;
    }

    private function volver(string $mensaje): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $mensaje]);

        return back();
    }
}
