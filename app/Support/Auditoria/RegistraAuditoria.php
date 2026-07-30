<?php

namespace App\Support\Auditoria;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;

/**
 * Auditoría centralizada y mínima (proyecto chico: sin observers ni colas).
 * Se invoca explícitamente dentro del mismo DB::transaction de cada acción.
 */
trait RegistraAuditoria
{
    protected function auditar(string $accion, Model $modelo, ?array $anterior, ?array $nuevo): void
    {
        Log::create([
            'entidad' => $modelo->getTable(),
            'entidad_id' => $modelo->getKey(),
            'accion' => $accion,
            'usuario_id' => auth()->id(),
            'login' => auth()->user()?->login,
            'session_id' => request()->session()->getId(),
            'anterior' => $anterior,
            'nuevo' => $nuevo,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'origen' => 'web',
        ]);
    }
}
