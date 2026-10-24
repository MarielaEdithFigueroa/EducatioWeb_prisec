<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\GuardarDocumentoIntegrante;
use App\Http\Requests\Academico\DocumentoIntegranteRequest;
use App\Models\Integrante;
use App\Models\TipoDocumentacionIntegrante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DocumentoIntegranteController extends CatalogoController
{
    public function guardar(DocumentoIntegranteRequest $request, Integrante $integrante, TipoDocumentacionIntegrante $tipoDocumentacionIntegrante, GuardarDocumentoIntegrante $guardar): RedirectResponse
    {
        $nuevo = null;
        try {
            DB::transaction(function () use ($request, $integrante, $tipoDocumentacionIntegrante, $guardar, &$nuevo): void {
                $integrante = Integrante::query()->lockForUpdate()->findOrFail($integrante->id);
                if (! $integrante->activo) {
                    throw ValidationException::withMessages(['archivo' => 'Reincorporá al integrante antes de modificar su documentación.']);
                }
                $registro = $integrante->documentacion()->where('tipo_documentacion_integrante_id', $tipoDocumentacionIntegrante->id)->first();
                if (! $tipoDocumentacionIntegrante->activo && $registro === null) {
                    throw ValidationException::withMessages(['archivo' => 'El tipo de documentación está inactivo.']);
                }
                if ($registro === null) {
                    $registro = $integrante->documentacion()->create(['tipo_documentacion_integrante_id' => $tipoDocumentacionIntegrante->id]);
                    $registro->refresh();
                    $this->auditar('create', $registro, null, $registro->attributesToArray());
                }
                $id = $request->validated('adjunto_id');
                $nuevo = $guardar->ejecutar($registro, $request->file('archivo'), $id === null ? null : (int) $id);
            });
        } catch (Throwable $error) {
            if ($nuevo !== null) {
                Storage::disk($nuevo->disco)->delete($nuevo->ruta);
            }
            throw $error;
        }
        $this->toast($request->isMethod('post') ? 'PDF adjuntado. Documentación marcada como presentada.' : 'PDF quitado. Se conservó el estado de presentación.');

        return back();
    }

    public function ver(Integrante $integrante, TipoDocumentacionIntegrante $tipoDocumentacionIntegrante): StreamedResponse
    {
        Gate::authorize('view', $integrante);
        $registro = $integrante->documentacion()->where('tipo_documentacion_integrante_id', $tipoDocumentacionIntegrante->id)->firstOrFail();
        $adjunto = $registro->adjuntos()->where('activo', true)->firstOrFail();
        Gate::authorize('view', $adjunto);
        $disco = Storage::disk($adjunto->disco);
        abort_unless($disco->exists($adjunto->ruta), 404);
        abort_unless($disco->mimeType($adjunto->ruta) === 'application/pdf', 404);

        return $disco->response($adjunto->ruta, $adjunto->nombre_original, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function quitar(DocumentoIntegranteRequest $request, Integrante $integrante, TipoDocumentacionIntegrante $tipoDocumentacionIntegrante, GuardarDocumentoIntegrante $guardar): RedirectResponse
    {
        return $this->guardar($request, $integrante, $tipoDocumentacionIntegrante, $guardar);
    }
}
