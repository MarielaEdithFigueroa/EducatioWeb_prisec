import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import type { ConfigAdjuntos } from '@/types/adjuntos';
import PdfDocumento from './pdf-documento';
import type { DocumentoIntegrante } from './types';

/** Cuántos documentos del legajo están presentados, para el contador del card y de la pestaña. */
export function contarPresentados(documentos: DocumentoIntegrante[]): string {
    const presentados = documentos.filter((item) => item.presentado).length;

    return `${presentados}/${documentos.length}`;
}

export default function ChecklistIntegrante({
    documentos,
    onChange,
    errors = {},
    integranteId,
    editablePdf,
    configuracionPdf,
    onArchivo,
    disabled,
}: {
    documentos: DocumentoIntegrante[];
    onChange?: (indice: number, presentado: boolean) => void;
    errors?: Record<string, string>;
    integranteId?: number;
    editablePdf?: boolean;
    configuracionPdf?: ConfigAdjuntos;
    onArchivo?: (
        indice: number,
        archivo: File | null,
        eliminar: boolean,
    ) => void;
    disabled?: boolean;
}) {
    if (documentos.length === 0) {
        return (
            <p className="text-sm text-muted-foreground">
                No hay tipos de documentación activos. Podés configurarlos desde
                el menú Académico.
            </p>
        );
    }

    return (
        <div className="divide-y divide-border">
            {documentos.map((item, indice) => {
                const errorFila =
                    errors[`documentacion.${indice}.presentado`] ||
                    errors[
                        `documentacion.${indice}.tipo_documentacion_integrante_id`
                    ];

                return (
                    <div
                        key={item.tipo_documentacion_integrante_id}
                        className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1 py-2.5"
                    >
                        <label
                            className="flex min-w-0 items-center gap-3"
                            htmlFor={`documento-${item.tipo_documentacion_integrante_id}`}
                        >
                            {onChange ? (
                                <Checkbox
                                    id={`documento-${item.tipo_documentacion_integrante_id}`}
                                    checked={item.presentado}
                                    disabled={disabled || Boolean(item.archivo)}
                                    onCheckedChange={(valor) =>
                                        onChange(indice, valor === true)
                                    }
                                />
                            ) : (
                                <Badge
                                    variant="outline"
                                    className={`w-24 shrink-0 justify-center ${
                                        item.presentado
                                            ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                            : 'border-educatio-warning/30 bg-educatio-warning/10 text-educatio-warning'
                                    }`}
                                >
                                    {item.presentado
                                        ? 'Presentado'
                                        : 'Pendiente'}
                                </Badge>
                            )}
                            <span className="min-w-0 text-sm">
                                {item.descripcion}
                                {!item.activo && (
                                    <span className="text-muted-foreground">
                                        {' '}
                                        (inactivo)
                                    </span>
                                )}
                            </span>
                        </label>
                        <PdfDocumento
                            documento={item}
                            integranteId={integranteId}
                            editable={editablePdf}
                            configuracion={configuracionPdf}
                            onChange={
                                onArchivo
                                    ? (archivo, eliminar) =>
                                          onArchivo(indice, archivo, eliminar)
                                    : undefined
                            }
                            error={
                                errors[`documentacion.${indice}.archivo`] ||
                                errors[
                                    `documentacion.${indice}.eliminar_archivo`
                                ]
                            }
                            disabled={disabled}
                        />
                        {errorFila && (
                            <p
                                role="alert"
                                className="col-span-full text-sm text-eure-error"
                            >
                                {errorFila}
                            </p>
                        )}
                    </div>
                );
            })}
            {errors.documentacion && (
                <p role="alert" className="pt-2 text-sm text-eure-error">
                    {errors.documentacion}
                </p>
            )}
        </div>
    );
}
