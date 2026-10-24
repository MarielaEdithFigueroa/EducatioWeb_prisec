import { useForm } from '@inertiajs/react';
import {
    DownloadIcon,
    EyeIcon,
    PaperclipIcon,
    PencilIcon,
    PlusIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import AdjuntoMiniatura from '@/components/adjunto-miniatura';
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
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import { formatDateTime } from '@/lib/date';
import { formatFileSize } from '@/lib/files';
import { formatId } from '@/lib/utils';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';

type Props = {
    acciones: {
        store: () => { url: string; method: 'post' };
        update: (id: number) => { url: string; method: 'patch' };
        desactivar: (id: number) => { url: string; method: 'patch' };
        reactivar: (id: number) => { url: string; method: 'patch' };
        descargar: (id: number) => string;
        miniatura: (id: number) => string;
    };
    titulo: string;
    descripcion: string;
    mensajeVacio: string;
    editable?: boolean;
    documentos: Adjunto[];
    configuracion: ConfigAdjuntos;
};

const ordenes = [
    { value: 'recientes', label: 'Más recientes' },
    { value: 'antiguos', label: 'Más antiguos' },
    { value: 'nombre', label: 'Nombre A–Z' },
    { value: 'tamano', label: 'Mayor tamaño' },
];
const documentosPorPagina = 12;

export default function DocumentosAdjuntos({
    acciones,
    titulo,
    descripcion,
    mensajeVacio,
    editable = true,
    documentos,
    configuracion,
}: Props) {
    const [incluirInactivos, setIncluirInactivos] = useState(false);
    const [busqueda, setBusqueda] = useState('');
    const [orden, setOrden] = useState('recientes');
    const [pagina, setPagina] = useState(1);
    const [nuevoAbierto, setNuevoAbierto] = useState(false);
    const [seleccionado, setSeleccionado] = useState<Adjunto | null>(null);
    const [editando, setEditando] = useState(false);
    const carga = useForm<{ archivo: File | null; observaciones: string }>({
        archivo: null,
        observaciones: '',
    });
    const edicion = useForm<{ observaciones: string; archivo?: never }>({
        observaciones: '',
    });
    const estado = useForm<{ archivo?: never }>({});
    const consulta = busqueda.trim().toLocaleLowerCase('es-AR');
    const visibles = documentos
        .filter(
            (documento) =>
                (incluirInactivos || documento.activo) &&
                `${formatId(documento.id, 6)} ${documento.nombre_original} ${documento.observaciones ?? ''} ${documento.usuario}`
                    .toLocaleLowerCase('es-AR')
                    .includes(consulta),
        )
        .sort((a, b) => {
            if (orden === 'nombre') {
                return (
                    a.nombre_original.localeCompare(
                        b.nombre_original,
                        'es-AR',
                    ) || b.id - a.id
                );
            }

            if (orden === 'tamano') {
                return b.tamano_bytes - a.tamano_bytes || b.id - a.id;
            }

            const porFecha =
                a.fecha_registro.localeCompare(b.fecha_registro) || a.id - b.id;

            return orden === 'antiguos' ? porFecha : -porFecha;
        });
    const paginas = Math.max(
        1,
        Math.ceil(visibles.length / documentosPorPagina),
    );
    const paginaActual = Math.min(pagina, paginas);
    const documentosPagina = visibles.slice(
        (paginaActual - 1) * documentosPorPagina,
        paginaActual * documentosPorPagina,
    );

    const abrirDocumento = (documento: Adjunto, editar = false) => {
        edicion.resetAndClearErrors();
        edicion.setData('observaciones', documento.observaciones ?? '');
        setSeleccionado(documento);
        setEditando(editar);
    };

    const adjuntar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        event.stopPropagation();

        if (!carga.data.archivo) {
            carga.setError('archivo', 'Seleccioná un archivo para adjuntar.');

            return;
        }

        if (
            carga.data.archivo &&
            carga.data.archivo.size > configuracion.max_bytes
        ) {
            carga.setError(
                'archivo',
                `El archivo supera el máximo de ${formatFileSize(configuracion.max_bytes)}.`,
            );

            return;
        }

        carga.submit(acciones.store(), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setNuevoAbierto(false);
                carga.resetAndClearErrors();
            },
        });
    };

    const guardarObservaciones = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        event.stopPropagation();

        if (!seleccionado) {
            return;
        }

        edicion.submit(acciones.update(seleccionado.id), {
            preserveScroll: true,
            onSuccess: () => setSeleccionado(null),
        });
    };

    const cambiarEstado = async (documento: Adjunto) => {
        const confirmado = await EureConfirmSwal()({
            title: documento.activo
                ? 'Desactivar documento'
                : 'Reactivar documento',
            text: documento.activo
                ? `“${documento.nombre_original}” quedará en el historial y podrá reactivarse.`
                : `“${documento.nombre_original}” volverá a estar disponible.`,
            confirmText: documento.activo ? 'Desactivar' : 'Reactivar',
        });

        if (!confirmado) {
            return;
        }

        estado.submit(
            documento.activo
                ? acciones.desactivar(documento.id)
                : acciones.reactivar(documento.id),
            { preserveScroll: true },
        );
    };

    return (
        <div>
            <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="flex items-center gap-2 font-semibold">
                        <PaperclipIcon
                            className="size-5 text-educatio-icon"
                            aria-hidden="true"
                        />
                        {titulo}
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {descripcion}
                    </p>
                </div>
                {editable && (
                    <EureButtonPrimary
                        type="button"
                        onClick={() => {
                            carga.resetAndClearErrors();
                            setNuevoAbierto(true);
                        }}
                    >
                        <PlusIcon className="size-4" />
                        Adjuntar archivo
                    </EureButtonPrimary>
                )}
            </div>

            <div className="mb-3 grid gap-3 sm:grid-cols-[minmax(0,1fr)_14rem]">
                <EureInputGroup
                    label="Buscar documentos"
                    name="buscar_documentos"
                    type="search"
                    onKeyDown={(event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                        }
                    }}
                    placeholder="Nombre, observaciones, usuario o número…"
                    value={busqueda}
                    onChange={(event) => {
                        setBusqueda(event.target.value);
                        setPagina(1);
                    }}
                />
                <EureSelectGroup
                    label="Ordenar por"
                    name="orden_documentos"
                    options={ordenes}
                    value={
                        ordenes.find((opcion) => opcion.value === orden) ??
                        ordenes[0]
                    }
                    onChange={(opcion) => {
                        setOrden(String(opcion?.value ?? 'recientes'));
                        setPagina(1);
                    }}
                />
            </div>
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3 text-sm">
                <label className="flex cursor-pointer items-center gap-2">
                    <input
                        type="checkbox"
                        checked={incluirInactivos}
                        onChange={(event) => {
                            setIncluirInactivos(event.target.checked);
                            setPagina(1);
                        }}
                        className="size-4 rounded border-border accent-primary focus-visible:ring-2 focus-visible:ring-ring"
                    />
                    Incluir inactivos
                </label>
                <p className="text-muted-foreground" role="status">
                    {visibles.length} de {documentos.length} documentos
                </p>
            </div>

            {documentos.length === 0 ? (
                <div className="rounded-lg border border-dashed border-border bg-muted/20 px-6 py-10 text-center">
                    <PaperclipIcon
                        className="mx-auto size-8 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <p className="mt-3 font-medium">{mensajeVacio}</p>
                    {editable && (
                        <p className="mt-1 text-sm text-muted-foreground">
                            Usá “Adjuntar archivo” para incorporar documentación
                            al legajo.
                        </p>
                    )}
                </div>
            ) : visibles.length === 0 ? (
                <p className="rounded-sm border border-dashed p-8 text-center text-sm text-muted-foreground">
                    No hay documentos que coincidan con los filtros.
                </p>
            ) : (
                <>
                    <ul className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        {documentosPagina.map((documento) => (
                            <li
                                key={documento.id}
                                className="flex min-w-0 flex-col overflow-hidden rounded-sm border border-border bg-card text-card-foreground"
                            >
                                <button
                                    type="button"
                                    className="group p-3 text-left focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset"
                                    onClick={() => abrirDocumento(documento)}
                                    aria-label={`Ver documento ${documento.nombre_original}`}
                                >
                                    <AdjuntoMiniatura
                                        documento={documento}
                                        src={acciones.miniatura(documento.id)}
                                    />
                                    <h3
                                        className="mt-3 line-clamp-2 text-sm font-semibold break-all group-hover:underline"
                                        title={documento.nombre_original}
                                    >
                                        {documento.nombre_original}
                                    </h3>
                                </button>
                                <div className="flex flex-1 flex-col gap-2 px-3 pb-3 text-xs text-muted-foreground">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <span className="tabular-nums">
                                            #{formatId(documento.id, 6)} ·{' '}
                                            {formatFileSize(
                                                documento.tamano_bytes,
                                            )}
                                        </span>
                                        {!documento.activo && (
                                            <Badge variant="outline">
                                                Inactivo
                                            </Badge>
                                        )}
                                    </div>
                                    <p
                                        className="line-clamp-2 break-words"
                                        title={
                                            documento.observaciones ?? undefined
                                        }
                                    >
                                        {documento.observaciones ||
                                            'Sin observaciones.'}
                                    </p>
                                    <p className="mt-auto pt-1">
                                        {formatDateTime(
                                            documento.fecha_registro,
                                        )}
                                        <span
                                            className="mt-1 block truncate"
                                            title={documento.usuario}
                                        >
                                            Por {documento.usuario}
                                        </span>
                                    </p>
                                </div>
                                <div className="flex flex-wrap items-center justify-between gap-1 border-t border-border px-2 py-1.5">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            abrirDocumento(documento)
                                        }
                                    >
                                        <EyeIcon className="size-4" />
                                        Ver
                                    </Button>
                                    <div className="flex items-center gap-1">
                                        {documento.activo && (
                                            <>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <a
                                                        href={acciones.descargar(
                                                            documento.id,
                                                        )}
                                                        title="Descargar archivo"
                                                        aria-label={`Descargar ${documento.nombre_original}`}
                                                    >
                                                        <DownloadIcon className="size-4" />
                                                    </a>
                                                </Button>
                                                {editable && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        title="Editar observaciones"
                                                        aria-label={`Editar observaciones de ${documento.nombre_original}`}
                                                        onClick={() =>
                                                            abrirDocumento(
                                                                documento,
                                                                true,
                                                            )
                                                        }
                                                    >
                                                        <PencilIcon className="size-4" />
                                                    </Button>
                                                )}
                                            </>
                                        )}
                                        {editable && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                title={
                                                    documento.activo
                                                        ? 'Desactivar documento'
                                                        : 'Reactivar documento'
                                                }
                                                aria-label={`${documento.activo ? 'Desactivar' : 'Reactivar'} ${documento.nombre_original}`}
                                                disabled={estado.processing}
                                                onClick={() =>
                                                    void cambiarEstado(
                                                        documento,
                                                    )
                                                }
                                            >
                                                {documento.activo ? (
                                                    <Trash2Icon className="size-4 text-eure-error" />
                                                ) : (
                                                    <RotateCcwIcon className="size-4" />
                                                )}
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                    {paginas > 1 && (
                        <nav
                            aria-label="Páginas de documentos"
                            className="mt-4 flex flex-wrap items-center justify-end gap-3 text-sm"
                        >
                            <span className="text-muted-foreground">
                                Página {paginaActual} de {paginas}
                            </span>
                            <EureButtonSecondary
                                type="button"
                                disabled={paginaActual === 1}
                                onClick={() => setPagina(paginaActual - 1)}
                            >
                                Anterior
                            </EureButtonSecondary>
                            <EureButtonSecondary
                                type="button"
                                disabled={paginaActual === paginas}
                                onClick={() => setPagina(paginaActual + 1)}
                            >
                                Siguiente
                            </EureButtonSecondary>
                        </nav>
                    )}
                </>
            )}

            {estado.errors.archivo && (
                <p role="alert" className="text-sm text-eure-error">
                    {estado.errors.archivo}
                </p>
            )}
            <Dialog
                open={nuevoAbierto}
                onOpenChange={(abierto) => {
                    if (!carga.processing) {
                        setNuevoAbierto(abierto);
                    }
                }}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Adjuntar documento</DialogTitle>
                        <DialogDescription>
                            Seleccioná el archivo e indicá las observaciones que
                            ayuden a identificarlo.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={adjuntar} className="space-y-4">
                        <EureArchivoGroup
                            name="archivo"
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
                            label="Observaciones"
                            name="observaciones_nuevo_documento"
                            value={carga.data.observaciones}
                            onChange={(event) =>
                                carga.setData(
                                    'observaciones',
                                    event.target.value,
                                )
                            }
                            maxLength={2000}
                            rows={4}
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
                                onClick={() => setNuevoAbierto(false)}
                            >
                                Cancelar
                            </EureButtonSecondary>
                            <EureButtonPrimary
                                type="submit"
                                loading={carga.processing}
                            >
                                Adjuntar archivo
                            </EureButtonPrimary>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={seleccionado !== null}
                onOpenChange={(abierto) => {
                    if (!abierto && !edicion.processing) {
                        setSeleccionado(null);
                    }
                }}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>
                            {editando
                                ? 'Editar observaciones'
                                : 'Ver documento'}
                        </DialogTitle>
                        <DialogDescription className="break-all">
                            {seleccionado?.nombre_original}
                        </DialogDescription>
                    </DialogHeader>
                    {seleccionado && (
                        <>
                            {!editando && (
                                <AdjuntoMiniatura
                                    key={seleccionado.id}
                                    documento={seleccionado}
                                    src={acciones.miniatura(seleccionado.id)}
                                    className="h-56"
                                />
                            )}
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt className="text-muted-foreground">
                                        Fecha de carga
                                    </dt>
                                    <dd>
                                        {formatDateTime(
                                            seleccionado.fecha_registro,
                                        )}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">
                                        Cargado por
                                    </dt>
                                    <dd>{seleccionado.usuario}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">
                                        Tamaño
                                    </dt>
                                    <dd>
                                        {formatFileSize(
                                            seleccionado.tamano_bytes,
                                        )}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">
                                        Estado
                                    </dt>
                                    <dd>
                                        {seleccionado.activo
                                            ? 'Activo'
                                            : 'Inactivo'}
                                    </dd>
                                </div>
                            </dl>
                            {editando ? (
                                <form
                                    onSubmit={guardarObservaciones}
                                    className="space-y-4"
                                >
                                    <EureTextareaGroup
                                        label="Observaciones"
                                        name="observaciones_documento"
                                        value={edicion.data.observaciones}
                                        onChange={(event) =>
                                            edicion.setData(
                                                'observaciones',
                                                event.target.value,
                                            )
                                        }
                                        maxLength={2000}
                                        rows={5}
                                        autoFocus
                                        error={Boolean(
                                            edicion.errors.observaciones,
                                        )}
                                        errorMessage={
                                            edicion.errors.observaciones
                                        }
                                        disabled={edicion.processing}
                                    />
                                    {edicion.errors.archivo && (
                                        <p
                                            role="alert"
                                            className="text-sm text-eure-error"
                                        >
                                            {edicion.errors.archivo}
                                        </p>
                                    )}
                                    <DialogFooter>
                                        <EureButtonSecondary
                                            type="button"
                                            disabled={edicion.processing}
                                            onClick={() =>
                                                setSeleccionado(null)
                                            }
                                        >
                                            Cancelar
                                        </EureButtonSecondary>
                                        <EureButtonPrimary
                                            type="submit"
                                            loading={edicion.processing}
                                        >
                                            Guardar observaciones
                                        </EureButtonPrimary>
                                    </DialogFooter>
                                </form>
                            ) : (
                                <>
                                    <div className="text-sm">
                                        <p className="font-medium">
                                            Observaciones
                                        </p>
                                        <p className="mt-1 break-words whitespace-pre-wrap text-muted-foreground">
                                            {seleccionado.observaciones ||
                                                'Sin observaciones.'}
                                        </p>
                                    </div>
                                    <DialogFooter>
                                        <EureButtonSecondary
                                            type="button"
                                            onClick={() =>
                                                setSeleccionado(null)
                                            }
                                        >
                                            Cerrar
                                        </EureButtonSecondary>
                                        {seleccionado.activo && (
                                            <>
                                                <EureButtonSecondary asChild>
                                                    <a
                                                        href={acciones.descargar(
                                                            seleccionado.id,
                                                        )}
                                                    >
                                                        <DownloadIcon className="size-4" />
                                                        Descargar
                                                    </a>
                                                </EureButtonSecondary>
                                                {editable && (
                                                    <EureButtonPrimary
                                                        type="button"
                                                        onClick={() =>
                                                            setEditando(true)
                                                        }
                                                    >
                                                        <PencilIcon className="size-4" />
                                                        Editar
                                                    </EureButtonPrimary>
                                                )}
                                            </>
                                        )}
                                    </DialogFooter>
                                </>
                            )}
                        </>
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
