import * as acciones from '@/actions/App/Http/Controllers/Academico/DocumentoAlumnoController';
import DocumentosAdjuntos from '@/components/documentos-adjuntos';
import EducatioCard from '@/components/educatio-card';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';

export default function Documentacion({
    alumnoId,
    documentos,
    configuracion,
}: {
    alumnoId: number;
    documentos: Adjunto[];
    configuracion: ConfigAdjuntos;
}) {
    const parametros = (adjunto: number) => ({ alumno: alumnoId, adjunto });

    return (
        <EducatioCard>
            <DocumentosAdjuntos
                documentos={documentos}
                configuracion={configuracion}
                titulo="Documentación del alumno"
                descripcion="Archivos del legajo y observaciones de cada documento."
                mensajeVacio="El alumno no tiene documentos adjuntos"
                acciones={{
                    store: () => acciones.store(alumnoId),
                    update: (id) => acciones.update(parametros(id)),
                    desactivar: (id) => acciones.desactivar(parametros(id)),
                    reactivar: (id) => acciones.reactivar(parametros(id)),
                    descargar: (id) => acciones.descargar.url(parametros(id)),
                    miniatura: (id) => acciones.miniatura.url(parametros(id)),
                }}
            />
        </EducatioCard>
    );
}
