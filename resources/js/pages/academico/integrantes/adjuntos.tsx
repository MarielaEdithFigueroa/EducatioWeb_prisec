import * as acciones from '@/actions/App/Http/Controllers/Academico/AdjuntoIntegranteController';
import DocumentosAdjuntos from '@/components/documentos-adjuntos';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';

export default function AdjuntosIntegrante({
    integranteId,
    documentos,
    configuracion,
    editable = true,
}: {
    integranteId?: number;
    documentos: Adjunto[];
    configuracion: ConfigAdjuntos;
    editable?: boolean;
}) {
    if (!integranteId) {
        return (
            <p
                role="status"
                className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground"
            >
                Guardá el integrante con “Guardar y continuar” para agregar
                adjuntos libres.
            </p>
        );
    }

    const parametros = (adjunto: number) => ({
        integrante: integranteId,
        adjunto,
    });

    return (
        <DocumentosAdjuntos
            documentos={documentos}
            configuracion={configuracion}
            editable={editable}
            titulo="Adjuntos libres"
            descripcion={
                editable
                    ? 'Archivos adicionales, sin asociar a un requisito del legajo. Se guardan al adjuntarlos.'
                    : 'Archivos adicionales del integrante. Disponibles para consulta.'
            }
            mensajeVacio="No hay adjuntos libres"
            acciones={{
                store: () => acciones.store(integranteId),
                update: (id) => acciones.update(parametros(id)),
                desactivar: (id) => acciones.desactivar(parametros(id)),
                reactivar: (id) => acciones.reactivar(parametros(id)),
                descargar: (id) => acciones.descargar.url(parametros(id)),
                miniatura: (id) => acciones.miniatura.url(parametros(id)),
            }}
        />
    );
}
