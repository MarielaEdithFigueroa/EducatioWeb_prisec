import { useForm } from '@inertiajs/react';
import { EyeIcon, PaperclipIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import EureArchivoGroup from '@/components/ui/eure/EureArchivoGroup';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import { formatFileSize } from '@/lib/files';
import {
    guardar,
    quitar,
    ver,
} from '@/routes/academico/integrantes/documentacion';
import type { ConfigAdjuntos } from '@/types/adjuntos';
import type { DocumentoIntegrante } from './types';

export default function PdfDocumento({
    documento,
    integranteId,
    editable,
    configuracion,
    onChange,
    error,
    disabled,
}: {
    documento: DocumentoIntegrante;
    integranteId?: number;
    editable?: boolean;
    configuracion?: ConfigAdjuntos;
    onChange?: (archivo: File | null, eliminar: boolean) => void;
    error?: string;
    disabled?: boolean;
}) {
    const [abierto, setAbierto] = useState(false);
    const carga = useForm<{ archivo: File | null; adjunto_id: number | null }>({
        archivo: null,
        adjunto_id: null,
    });
    const estado = useForm<{ adjunto_id: number | null; archivo: null }>({
        adjunto_id: null,
        archivo: null,
    });
    const ocupado = disabled || carga.processing || estado.processing;
    const actual = documento.adjunto;
    const parametros = {
        integrante: integranteId ?? 0,
        tipoDocumentacionIntegrante: documento.tipo_documentacion_integrante_id,
    };
    const nombre =
        documento.archivo?.name ??
        (documento.eliminar_archivo ? null : actual?.nombre_original);
    const mensajeError =
        error || estado.errors.archivo || estado.errors.adjunto_id;

    const adjuntar = (event: FormEvent) => {
        event.preventDefault();
        event.stopPropagation();

        if (!carga.data.archivo) {
            carga.setError('archivo', 'Seleccioná un PDF.');

            return;
        }

        if (onChange) {
            onChange(carga.data.archivo, false);
            setAbierto(false);

            return;
        }

        carga.transform((datos) => ({
            ...datos,
            adjunto_id: actual?.id ?? null,
        }));
        carga.submit(guardar(parametros), {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setAbierto(false);
                carga.resetAndClearErrors();
            },
        });
    };
    const quitarPdf = async () => {
        if (
            !(await EureConfirmSwal()({
                title: 'Quitar PDF',
                text: 'Se quitará el archivo de esta fila. El estado de presentación se conservará.',
                confirmText: 'Quitar PDF',
            }))
        ) {
            return;
        }

        if (onChange) {
            onChange(null, Boolean(actual));

            return;
        }

        estado.transform(() => ({ adjunto_id: actual?.id ?? null }));
        estado.submit(quitar(parametros), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="min-w-0 space-y-1 print:hidden">
            <div className="flex flex-wrap items-center gap-1">
                {actual && integranteId && !documento.eliminar_archivo && (
                    <Button asChild variant="ghost" size="sm">
                        <a
                            href={ver.url(parametros, {
                                query: { version: actual.id },
                            })}
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label={`Ver PDF de ${documento.descripcion}`}
                        >
                            <EyeIcon />
                            Ver PDF
                        </a>
                    </Button>
                )}
                {editable && configuracion && (
                    <Dialog
                        open={abierto}
                        onOpenChange={(valor) => {
                            if (!ocupado) {
                                setAbierto(valor);

                                if (valor) {
                                    carga.resetAndClearErrors();
                                    estado.clearErrors();
                                }
                            }
                        }}
                    >
                        <DialogTrigger asChild>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                disabled={ocupado}
                                aria-label={`${nombre ? 'Reemplazar' : 'Adjuntar'} PDF de ${documento.descripcion}`}
                            >
                                <PaperclipIcon />
                                {nombre ? 'Reemplazar' : 'Adjuntar PDF'}
                            </Button>
                        </DialogTrigger>
                        <DialogContent
                            onEscapeKeyDown={(event) => {
                                if (ocupado) {
                                    event.preventDefault();
                                }
                            }}
                            onInteractOutside={(event) => {
                                if (ocupado) {
                                    event.preventDefault();
                                }
                            }}
                        >
                            <DialogHeader>
                                <DialogTitle>
                                    {nombre ? 'Reemplazar PDF' : 'Adjuntar PDF'}{' '}
                                    · {documento.descripcion}
                                </DialogTitle>
                                <DialogDescription>
                                    Un PDF por documento, hasta{' '}
                                    {formatFileSize(configuracion.max_bytes)}.
                                    Adjuntarlo marca la documentación como
                                    presentada.
                                    {onChange
                                        ? ' Se aplicará al guardar el legajo.'
                                        : ''}
                                    {actual
                                        ? ' El PDF anterior se conservará en el historial.'
                                        : ''}
                                </DialogDescription>
                            </DialogHeader>
                            <form onSubmit={adjuntar} className="space-y-4">
                                <EureArchivoGroup
                                    name={`pdf-${documento.tipo_documentacion_integrante_id}`}
                                    configuracion={configuracion}
                                    value={carga.data.archivo}
                                    onChange={(archivo) =>
                                        carga.setData('archivo', archivo)
                                    }
                                    error={
                                        carga.errors.archivo ||
                                        carga.errors.adjunto_id
                                    }
                                    disabled={ocupado}
                                />
                                {carga.progress && (
                                    <p role="status" className="text-sm">
                                        Subiendo: {carga.progress.percentage}%
                                    </p>
                                )}
                                <DialogFooter>
                                    <EureButtonSecondary
                                        type="button"
                                        disabled={ocupado}
                                        onClick={() => setAbierto(false)}
                                    >
                                        Cancelar
                                    </EureButtonSecondary>
                                    <EureButtonPrimary
                                        type="submit"
                                        loading={carga.processing}
                                        disabled={
                                            ocupado || !carga.data.archivo
                                        }
                                    >
                                        {onChange ? 'Aceptar' : 'Adjuntar'}
                                    </EureButtonPrimary>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                )}
                {editable && nombre && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        disabled={ocupado}
                        onClick={() => void quitarPdf()}
                        aria-label={`Quitar PDF de ${documento.descripcion}`}
                    >
                        <Trash2Icon className="text-eure-error" />
                        Quitar
                    </Button>
                )}
                {documento.eliminar_archivo && onChange && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        disabled={ocupado}
                        onClick={() => onChange(null, false)}
                    >
                        Conservar PDF
                    </Button>
                )}
            </div>
            {nombre && (
                <p
                    className="max-w-xs truncate text-xs text-muted-foreground"
                    title={nombre}
                >
                    {nombre}
                    {documento.archivo ? ' · pendiente de guardar' : ''}
                </p>
            )}
            {documento.eliminar_archivo && (
                <p className="text-xs text-educatio-warning">
                    Se quitará al guardar.
                </p>
            )}
            {mensajeError && (
                <p role="alert" className="text-sm text-eure-error">
                    {mensajeError}
                </p>
            )}
        </div>
    );
}
