<?php

namespace App\Actions;

use App\Models\Adjunto;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GuardarAdjunto
{
    use RegistraAuditoria;

    public function ejecutar(BelongsToMany $destino, UploadedFile $archivo, ?string $observaciones): Adjunto
    {
        $disco = 'adjuntos';
        $nombre = basename(str_replace('\\', '/', $archivo->getClientOriginalName()));
        $nombre = preg_replace('/[\x00-\x1F\x7F]/u', '', $nombre) ?: 'archivo';
        $directorio = 'archivos/'.Str::uuid();
        $ruta = null;

        try {
            return DB::transaction(function () use ($destino, $archivo, $observaciones, $disco, &$ruta, $nombre, $directorio): Adjunto {
                $adjunto = $destino->create([
                    'nombre_original' => mb_substr($nombre, 0, 255),
                    'disco' => $disco,
                    'ruta' => $directorio.'/.pendiente',
                    'mime' => $archivo->getMimeType() ?: 'application/octet-stream',
                    'tamano_bytes' => $archivo->getSize(),
                    'observaciones' => $observaciones,
                    'fecha_registro' => now(),
                    'usuario_id' => auth()->id(),
                ]);

                $nombreGuardado = $this->nombreAlmacenamiento($archivo, $nombre, $adjunto);
                $ruta = $directorio.'/'.$nombreGuardado;

                if ($archivo->storeAs($directorio, $nombreGuardado, $disco) === false) {
                    throw new RuntimeException('No se pudo guardar el archivo adjunto.');
                }

                $adjunto->update(['ruta' => $ruta]);
                $adjunto->refresh();

                $this->auditar('create', $adjunto, null, [
                    ...$adjunto->attributesToArray(),
                    'disco' => $disco,
                    'ruta' => $ruta,
                    'vinculo' => [
                        'entidad' => $destino->getParent()->getTable(),
                        'entidad_id' => $destino->getParent()->getKey(),
                    ],
                ]);

                return $adjunto;
            });
        } catch (Throwable $error) {
            if ($ruta !== null) {
                try {
                    if (! Storage::disk($disco)->delete($ruta)) {
                        report(new RuntimeException('No se pudo limpiar el adjunto de una operación fallida.'));
                    }
                } catch (Throwable $errorLimpieza) {
                    report($errorLimpieza);
                }
            }

            throw $error;
        }
    }

    private function nombreAlmacenamiento(UploadedFile $archivo, string $nombreOriginal, Adjunto $adjunto): string
    {
        $base = pathinfo($nombreOriginal, PATHINFO_FILENAME);
        $base = preg_replace('/[^\p{L}\p{M}\p{N}._-]/u', '_', $base) ?? '';
        $base = trim(mb_strcut($base, 0, 140, 'UTF-8'), ' .');
        $base = $base !== '' ? $base : 'archivo';
        $extension = $archivo->getClientOriginalExtension();

        return $adjunto->fecha_registro->format('Ymd_His').'_'.$base.'_'.sprintf('%06d', $adjunto->id).'.'.$extension;
    }
}
