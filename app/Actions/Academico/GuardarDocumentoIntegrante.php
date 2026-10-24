<?php

namespace App\Actions\Academico;

use App\Actions\GuardarAdjunto;
use App\Models\Adjunto;
use App\Models\IntegranteDocumentacion;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class GuardarDocumentoIntegrante
{
    use RegistraAuditoria;

    public function __construct(private GuardarAdjunto $guardar) {}

    /** Se ejecuta en la transacción del legajo, con el integrante bloqueado. */
    public function ejecutar(IntegranteDocumentacion $registro, ?UploadedFile $archivo, ?int $esperado, string $campoError = 'archivo'): ?Adjunto
    {
        $actual = $registro->adjuntos()->where('activo', true)->first();
        if ($actual?->id !== $esperado) {
            throw ValidationException::withMessages([$campoError => 'El PDF cambió desde que abriste la ficha. Recargá para ver la versión actual.']);
        }
        if ($archivo !== null) {
            Gate::authorize('create', Adjunto::class);
        }
        if ($actual !== null) {
            Gate::authorize('update', $actual);
            $anterior = $actual->attributesToArray();
            $actual->update(['activo' => false]);
            $this->auditar('deactivate', $actual, $anterior, $actual->attributesToArray());
        }
        if ($archivo === null) {
            return null;
        }
        if (! $registro->presentado) {
            $anterior = $registro->attributesToArray();
            $registro->update(['presentado' => true]);
            $this->auditar('update', $registro, $anterior, $registro->attributesToArray());
        }

        return $this->guardar->ejecutar($registro->adjuntos(), $archivo, null);
    }
}
