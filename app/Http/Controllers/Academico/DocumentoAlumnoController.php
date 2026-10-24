<?php

namespace App\Http\Controllers\Academico;

use App\Actions\GuardarAdjunto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\ActualizarDocumentoAlumnoRequest;
use App\Http\Requests\Academico\CambiarEstadoDocumentoAlumnoRequest;
use App\Http\Requests\Academico\GuardarDocumentoAlumnoRequest;
use App\Models\Adjunto;
use App\Models\Alumno;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoAlumnoController extends Controller
{
    use RegistraAuditoria;

    public function store(GuardarDocumentoAlumnoRequest $request, Alumno $alumno, GuardarAdjunto $guardar): RedirectResponse
    {
        $guardar->ejecutar($alumno->adjuntos(), $request->file('archivo'), $request->validated('observaciones'));

        return $this->volver($alumno, 'Documento adjuntado.');
    }

    public function descargar(Alumno $alumno, Adjunto $adjunto): StreamedResponse
    {
        Gate::authorize('view', $alumno);
        Gate::authorize('view', $adjunto);

        abort_unless($adjunto->activo, 404);
        abort_unless(Storage::disk($adjunto->disco)->exists($adjunto->ruta), 404);

        return Storage::disk($adjunto->disco)->download($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function miniatura(Alumno $alumno, Adjunto $adjunto): StreamedResponse
    {
        Gate::authorize('view', $alumno);
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

    public function update(ActualizarDocumentoAlumnoRequest $request, Alumno $alumno, Adjunto $adjunto): RedirectResponse
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

        return $this->volver($alumno, 'Observaciones actualizadas.');
    }

    public function desactivar(CambiarEstadoDocumentoAlumnoRequest $request, Alumno $alumno, Adjunto $adjunto): RedirectResponse
    {
        $this->cambiarEstado($adjunto, false);

        return $this->volver($alumno, 'Documento desactivado.');
    }

    public function reactivar(CambiarEstadoDocumentoAlumnoRequest $request, Alumno $alumno, Adjunto $adjunto): RedirectResponse
    {
        $this->cambiarEstado($adjunto, true);

        return $this->volver($alumno, 'Documento reactivado.');
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

    private function volver(Alumno $alumno, string $mensaje): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $mensaje]);

        return to_route('academico.alumnos.show', ['alumno' => $alumno, 'seccion' => 'documentacion']);
    }
}
