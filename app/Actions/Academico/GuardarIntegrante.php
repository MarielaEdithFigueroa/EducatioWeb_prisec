<?php

namespace App\Actions\Academico;

use App\Models\Integrante;
use App\Models\TipoDocumentacionIntegrante;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class GuardarIntegrante
{
    use RegistraAuditoria;

    public function __construct(private GuardarDocumentoIntegrante $guardarDocumento, private GuardarRelacionesIntegrante $guardarRelaciones) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(Integrante $integrante, array $datos, ?UploadedFile $foto): Integrante
    {
        $nuevaRuta = null;
        $anteriorRuta = null;
        $nuevosAdjuntos = [];
        try {
            $integrante = DB::transaction(function () use ($integrante, $datos, $foto, &$nuevaRuta, &$anteriorRuta, &$nuevosAdjuntos): Integrante {
                if ($integrante->exists) {
                    $integrante = Integrante::query()->lockForUpdate()->findOrFail($integrante->id);
                    if (! $integrante->activo) {
                        throw ValidationException::withMessages(['legajo' => 'Reincorporá al integrante antes de modificar su legajo.']);
                    }
                }
                $nuevo = ! $integrante->exists;
                $anterior = $nuevo ? null : $integrante->getAttributes();
                $anteriorRuta = $integrante->foto_ruta;
                $documentacion = $datos['documentacion'] ?? [];
                $funciones = $datos['funciones_ids'] ?? null;
                $referencias = $datos['referencias_laborales'] ?? [];
                $contactos = $datos['contactos'] ?? [];
                $bajasReferencias = $datos['referencias_laborales_bajas'] ?? [];
                $bajasContactos = $datos['contactos_bajas'] ?? [];
                unset($datos['referencias_laborales'], $datos['contactos'], $datos['referencias_laborales_bajas'], $datos['contactos_bajas']);
                $eliminarFoto = (bool) ($datos['eliminar_foto'] ?? false);
                unset($datos['foto'], $datos['documentacion'], $datos['eliminar_foto'], $datos['continuar'], $datos['filtros'], $datos['funciones_ids'], $datos['seccion']);
                foreach (Integrante::TELEFONOS_CON_TIPO as $campo) {
                    unset($datos[$campo.'_es_fijo']);
                }
                $integrante->fill($datos);
                if ($foto !== null) {
                    $nuevaRuta = $foto->store('integrantes/fotos', 'adjuntos');
                    if ($nuevaRuta === false) {
                        throw new RuntimeException('No se pudo guardar la foto.');
                    }
                    $integrante->foto_ruta = $nuevaRuta;
                } elseif ($eliminarFoto) {
                    $integrante->foto_ruta = null;
                }
                if ($nuevo || $integrante->isDirty()) {
                    $integrante->save();
                    $integrante->refresh();
                    $this->auditar($nuevo ? 'create' : 'update', $integrante, $anterior, $integrante->getAttributes());
                }
                if ($funciones !== null) {
                    $this->guardarFunciones($integrante, $funciones);
                }
                $this->guardarRelaciones->ejecutar($integrante->referenciasLaborales(), $referencias, $bajasReferencias, 'referencias_laborales');
                $this->guardarRelaciones->ejecutar($integrante->contactos(), $contactos, $bajasContactos, 'contactos');
                $presentaciones = collect($documentacion)->keyBy('tipo_documentacion_integrante_id');
                $existentes = $integrante->documentacion()->get()->keyBy('tipo_documentacion_integrante_id');
                $tipos = TipoDocumentacionIntegrante::query()->where('activo', true)
                    ->orWhereIn('id', $existentes->keys())->pluck('id');
                foreach ($tipos as $tipoId) {
                    $registro = $existentes->get($tipoId) ?? $integrante->documentacion()->make(['tipo_documentacion_integrante_id' => $tipoId]);
                    $eraNuevo = ! $registro->exists;
                    $previo = $eraNuevo ? null : $registro->attributesToArray();
                    if ($presentaciones->has($tipoId)) {
                        $registro->presentado = (bool) $presentaciones[$tipoId]['presentado'];
                    }
                    if ($eraNuevo || $registro->isDirty()) {
                        $registro->save();
                        $registro->refresh();
                        $this->auditar($eraNuevo ? 'create' : 'update', $registro, $previo, $registro->attributesToArray());
                    }
                    $presentacion = $presentaciones->get($tipoId, []);
                    $archivo = $presentacion['archivo'] ?? null;
                    if ($archivo !== null || ($presentacion['eliminar_archivo'] ?? false)) {
                        $indice = array_search($tipoId, array_column($documentacion, 'tipo_documentacion_integrante_id'));
                        $esperado = $presentacion['adjunto_id'] ?? null;
                        $adjunto = $this->guardarDocumento->ejecutar($registro, $archivo, $esperado === null ? null : (int) $esperado, "documentacion.$indice.archivo");
                        if ($adjunto !== null) {
                            $nuevosAdjuntos[] = $adjunto;
                        }
                    }
                }

                return $integrante;
            });
        } catch (Throwable $error) {
            foreach ($nuevosAdjuntos as $adjunto) {
                $this->limpiarArchivo($adjunto->ruta);
            }
            if (is_string($nuevaRuta)) {
                $this->limpiarArchivo($nuevaRuta);
            }
            if ($error instanceof UniqueConstraintViolationException && Integrante::query()->where('legajo', $datos['legajo'])->when($integrante->exists, fn ($query) => $query->where('id', '!=', $integrante->id))->exists()) {
                throw ValidationException::withMessages(['legajo' => 'El legajo ya está en uso. Elegí otro número.']);
            }
            throw $error;
        }
        if ($anteriorRuta && $anteriorRuta !== $integrante->foto_ruta) {
            $this->limpiarArchivo($anteriorRuta);
        }

        return $integrante;
    }

    /** @param list<int|string> $funciones */
    private function guardarFunciones(Integrante $integrante, array $funciones): void
    {
        $seleccionadas = array_map('intval', $funciones);
        $existentes = $integrante->asignacionesFunciones()->get()->keyBy('funcion_equipo_id');
        foreach (array_unique([...$existentes->keys()->all(), ...$seleccionadas]) as $funcionId) {
            $registro = $existentes->get($funcionId) ?? $integrante->asignacionesFunciones()->make(['funcion_equipo_id' => $funcionId]);
            $nuevo = ! $registro->exists;
            $anterior = $nuevo ? null : $registro->attributesToArray();
            $registro->activo = in_array($funcionId, $seleccionadas, true);
            if ($nuevo || $registro->isDirty()) {
                $registro->save();
                $registro->refresh();
                $this->auditar($nuevo ? 'create' : ($registro->activo ? 'reactivate' : 'deactivate'), $registro, $anterior, $registro->attributesToArray());
            }
        }
    }

    private function limpiarArchivo(string $ruta): void
    {
        try {
            if (! Storage::disk('adjuntos')->delete($ruta)) {
                report(new RuntimeException('No se pudo eliminar un archivo descartado.'));
            }
        } catch (Throwable $error) {
            report($error);
        }
    }
}
