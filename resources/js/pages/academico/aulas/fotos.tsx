import { useForm } from '@inertiajs/react';
import {
    CameraIcon,
    DownloadIcon,
    PencilIcon,
    PlusIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import {
    descargar,
    desactivar,
    miniatura,
    reactivar,
    store,
    update,
} from '@/actions/App/Http/Controllers/Academico/FotoAulaController';
import AdjuntoMiniatura from '@/components/adjunto-miniatura';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import EureArchivoGroup from '@/components/ui/eure/EureArchivoGroup';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import { formatDateTime } from '@/lib/date';
import { formatFileSize } from '@/lib/files';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';

type Props = {
    aulaId: number;
    fotos: Adjunto[];
    configuracion: ConfigAdjuntos;
};

export default function FotosAula({ aulaId, fotos, configuracion }: Props) {
    const [nuevaAbierta, setNuevaAbierta] = useState(false);
    const [incluirInactivas, setIncluirInactivas] = useState(false);
    const [seleccionadaId, setSeleccionadaId] = useState<number | null>(null);
    const [editando, setEditando] = useState(false);
    const carga = useForm<{ archivo: File | null; observaciones: string }>({
        archivo: null,
        observaciones: '',
    });
    const edicion = useForm({ observaciones: '' });
    const estado = useForm({});
    const seleccionada = fotos.find((foto) => foto.id === seleccionadaId);
    const visibles = fotos.filter((foto) => incluirInactivas || foto.activo);
    const parametros = (foto: Adjunto) => ({ aula: aulaId, adjunto: foto.id });

    const abrir = (foto: Adjunto, editar = false) => {
        edicion.resetAndClearErrors();
        edicion.setData('observaciones', foto.observaciones ?? '');
        setEditando(editar);
        setSeleccionadaId(foto.id);
    };

    const adjuntar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!carga.data.archivo) {
            carga.setError('archivo', 'Seleccioná una foto para adjuntar.');

            return;
        }

        carga.submit(store(aulaId), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setNuevaAbierta(false);
                carga.resetAndClearErrors();
            },
        });
    };

    const guardarDescripcion = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!seleccionada) {
            return;
        }

        edicion.submit(update(parametros(seleccionada)), {
            preserveScroll: true,
            onSuccess: () => setSeleccionadaId(null),
        });
    };

    const cambiarEstado = async (foto: Adjunto) => {
        const confirmado = await EureConfirmSwal()({
            title: foto.activo ? 'Desactivar foto' : 'Reactivar foto',
            text: foto.activo
                ? 'La foto quedará en el historial y podrá reactivarse. El archivo se conserva.'
                : 'La foto volverá a estar disponible en la galería.',
            confirmText: foto.activo ? 'Desactivar' : 'Reactivar',
        });

        if (!confirmado) {
            return;
        }

        estado.submit(
            foto.activo
                ? desactivar(parametros(foto))
                : reactivar(parametros(foto)),
            { preserveScroll: true },
        );
    };

    return (
        <EducatioCard>
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 className="flex items-center gap-2 font-semibold">
                    <CameraIcon
                        className="size-5 text-educatio-icon"
                        aria-hidden="true"
                    />
                    Fotos del aula
                </h2>
                <EureButtonPrimary
                    onClick={() => {
                        carga.resetAndClearErrors();
                        setNuevaAbierta(true);
                    }}
                >
                    <PlusIcon className="size-4" /> Agregar foto
                </EureButtonPrimary>
            </div>
            <label className="mb-4 flex w-fit items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    className="size-4 accent-primary"
                    checked={incluirInactivas}
                    onChange={(event) =>
                        setIncluirInactivas(event.target.checked)
                    }
                />
                Incluir fotos inactivas
            </label>
            {visibles.length === 0 ? (
                <p className="rounded-sm border border-dashed p-6 text-center text-sm text-muted-foreground">
                    No hay fotos {incluirInactivas ? 'adjuntas' : 'activas'}.
                    Usá «Agregar foto» para cargar una imagen del aula.
                </p>
            ) : (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {visibles.map((foto) => (
                        <article
                            key={foto.id}
                            className="min-w-0 rounded-sm border bg-background p-3"
                        >
                            <button
                                type="button"
                                className="w-full rounded-sm text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                onClick={() => abrir(foto)}
                                aria-label={`Ver foto: ${foto.nombre_original}`}
                            >
                                <AdjuntoMiniatura
                                    documento={foto}
                                    src={miniatura.url(parametros(foto))}
                                />
                                <p
                                    className="mt-2 truncate text-sm font-medium"
                                    title={foto.nombre_original}
                                >
                                    {foto.nombre_original}
                                </p>
                            </button>
                            {!foto.activo && (
                                <Badge variant="outline" className="mt-2">
                                    Inactiva
                                </Badge>
                            )}
                            {foto.observaciones && (
                                <p className="mt-2 line-clamp-2 text-sm break-words text-muted-foreground">
                                    {foto.observaciones}
                                </p>
                            )}
                            <p className="mt-2 text-xs text-muted-foreground">
                                {formatDateTime(foto.fecha_registro)}
                            </p>
                            <div className="mt-3 flex justify-end gap-1">
                                {foto.activo && (
                                    <>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            asChild
                                        >
                                            <a
                                                href={descargar.url(
                                                    parametros(foto),
                                                )}
                                                title="Descargar foto"
                                                aria-label={`Descargar ${foto.nombre_original}`}
                                            >
                                                <DownloadIcon className="size-4" />
                                            </a>
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => abrir(foto, true)}
                                            title="Editar descripción"
                                            aria-label={`Editar descripción de ${foto.nombre_original}`}
                                        >
                                            <PencilIcon className="size-4" />
                                        </Button>
                                    </>
                                )}
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    disabled={estado.processing}
                                    onClick={() => void cambiarEstado(foto)}
                                    title={
                                        foto.activo
                                            ? 'Desactivar foto'
                                            : 'Reactivar foto'
                                    }
                                    aria-label={`${foto.activo ? 'Desactivar' : 'Reactivar'} ${foto.nombre_original}`}
                                >
                                    {foto.activo ? (
                                        <Trash2Icon className="size-4" />
                                    ) : (
                                        <RotateCcwIcon className="size-4" />
                                    )}
                                </Button>
                            </div>
                        </article>
                    ))}
                </div>
            )}

            <Dialog
                open={nuevaAbierta}
                onOpenChange={(abierto) => {
                    if (!carga.processing) {
                        setNuevaAbierta(abierto);
                    }
                }}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Agregar foto</DialogTitle>
                        <DialogDescription>
                            Seleccioná o arrastrá una imagen. Podés agregar una
                            descripción para identificarla.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={adjuntar} className="space-y-4">
                        <EureArchivoGroup
                            name="foto_aula"
                            configuracion={configuracion}
                            value={carga.data.archivo}
                            onChange={(archivo) => {
                                carga.clearErrors('archivo');
                                carga.setData('archivo', archivo);
                            }}
                            error={carga.errors.archivo}
                            disabled={carga.processing}
                        />
                        <EureTextareaGroup
                            label="Descripción (opcional)"
                            name="descripcion_nueva_foto"
                            value={carga.data.observaciones}
                            onChange={(event) =>
                                carga.setData(
                                    'observaciones',
                                    event.target.value,
                                )
                            }
                            maxLength={2000}
                            rows={3}
                            error={Boolean(carga.errors.observaciones)}
                            errorMessage={carga.errors.observaciones}
                            disabled={carga.processing}
                        />
                        {carga.progress && (
                            <div role="status" className="text-sm">
                                <progress
                                    className="w-full accent-primary"
                                    value={carga.progress.percentage ?? 0}
                                    max={100}
                                    aria-label="Progreso de carga"
                                />
                                Cargando: {carga.progress.percentage ?? 0}%
                            </div>
                        )}
                        <DialogFooter>
                            <EureButtonSecondary
                                type="button"
                                disabled={carga.processing}
                                onClick={() => setNuevaAbierta(false)}
                            >
                                Cancelar
                            </EureButtonSecondary>
                            <EureButtonPrimary
                                type="submit"
                                loading={carga.processing}
                            >
                                Adjuntar foto
                            </EureButtonPrimary>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
            <Dialog
                open={Boolean(seleccionada)}
                onOpenChange={(abierto) => {
                    if (!abierto && !edicion.processing) {
                        setSeleccionadaId(null);
                    }
                }}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>
                            {editando ? 'Editar descripción' : 'Ver foto'}
                        </DialogTitle>
                        <DialogDescription className="break-all">
                            {seleccionada?.nombre_original}
                        </DialogDescription>
                    </DialogHeader>
                    {seleccionada && (
                        <>
                            <AdjuntoMiniatura
                                key={seleccionada.id}
                                documento={seleccionada}
                                src={miniatura.url(parametros(seleccionada))}
                                className="h-[40vh]"
                            />
                            <p className="text-sm text-muted-foreground">
                                {formatDateTime(seleccionada.fecha_registro)} ·{' '}
                                {seleccionada.usuario} ·{' '}
                                {formatFileSize(seleccionada.tamano_bytes)}
                            </p>
                            {editando && seleccionada.activo ? (
                                <form
                                    onSubmit={guardarDescripcion}
                                    className="space-y-4"
                                >
                                    <EureTextareaGroup
                                        label="Descripción (opcional)"
                                        name="descripcion_foto"
                                        value={edicion.data.observaciones}
                                        onChange={(event) =>
                                            edicion.setData(
                                                'observaciones',
                                                event.target.value,
                                            )
                                        }
                                        maxLength={2000}
                                        rows={3}
                                        autoFocus
                                        error={Boolean(
                                            edicion.errors.observaciones,
                                        )}
                                        errorMessage={
                                            edicion.errors.observaciones
                                        }
                                        disabled={edicion.processing}
                                    />
                                    <DialogFooter>
                                        <EureButtonSecondary
                                            type="button"
                                            disabled={edicion.processing}
                                            onClick={() =>
                                                setSeleccionadaId(null)
                                            }
                                        >
                                            Cancelar
                                        </EureButtonSecondary>
                                        <EureButtonPrimary
                                            type="submit"
                                            loading={edicion.processing}
                                        >
                                            Guardar descripción
                                        </EureButtonPrimary>
                                    </DialogFooter>
                                </form>
                            ) : (
                                <>
                                    <p className="text-sm break-words whitespace-pre-wrap">
                                        {seleccionada.observaciones ||
                                            'Sin descripción.'}
                                    </p>
                                    {!seleccionada.activo && (
                                        <p className="text-sm text-muted-foreground">
                                            Foto inactiva. Reactivala para
                                            volver a verla y descargarla.
                                        </p>
                                    )}
                                    <DialogFooter>
                                        <EureButtonSecondary
                                            onClick={() =>
                                                setSeleccionadaId(null)
                                            }
                                        >
                                            Cerrar
                                        </EureButtonSecondary>
                                        {seleccionada.activo && (
                                            <EureButtonSecondary asChild>
                                                <a
                                                    href={descargar.url(
                                                        parametros(
                                                            seleccionada,
                                                        ),
                                                    )}
                                                >
                                                    <DownloadIcon className="size-4" />{' '}
                                                    Descargar
                                                </a>
                                            </EureButtonSecondary>
                                        )}
                                    </DialogFooter>
                                </>
                            )}
                        </>
                    )}
                </DialogContent>
            </Dialog>
        </EducatioCard>
    );
}
