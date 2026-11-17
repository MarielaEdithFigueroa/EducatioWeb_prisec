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
        $conSesion = request()->hasSession();

        Log::create([
            'entidad' => $modelo->getTable(),
            'entidad_id' => $modelo->getKey(),
            'accion' => $accion,
            'usuario_id' => $conSesion ? auth()->id() : null,
            'login' => $conSesion ? auth()->user()?->login : null,
            'session_id' => $conSesion ? request()->session()->getId() : null,
            'anterior' => $anterior,
            'nuevo' => $nuevo,
            'ip' => $conSesion ? request()->ip() : null,
            'user_agent' => $conSesion ? request()->userAgent() : null,
            'origen' => $conSesion ? 'web' : 'consola',
        ]);
    }
}
