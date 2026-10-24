import { Link, useForm, useHttp } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    LinkIcon,
    PencilIcon,
    PlusIcon,
    SearchIcon,
    UsersRoundIcon,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import AlumnoResponsableController from '@/actions/App/Http/Controllers/Academico/AlumnoResponsableController';
import ResponsableController from '@/actions/App/Http/Controllers/Academico/ResponsableController';
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
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import { formatDate, toDateOnlyString } from '@/lib/date';
import { formatId } from '@/lib/utils';
import FormularioVinculo from '../../responsables/formulario-vinculo';
import type { DatosVinculo, TipoVinculo } from '../../responsables/types';
import { vinculoVacio } from '../../responsables/types';
import type { ResponsableVinculado } from './types';

type AlumnoResumen = { id: number; apellido: string; nombre: string };

type VinculoEncontrado = Omit<
    DatosVinculo,
    'fecha_desde' | 'fecha_hasta' | 'observaciones'
> & {
    id: number;
    activo: boolean;
    fecha_desde: string | null;
    fecha_hasta: string | null;
    observaciones: string | null;
};

type ResponsableEncontrado = {
    id: number;
    activo: boolean;
    apellido: string;
    nombre: string;
    nombre_elegido: string | null;
    tipo_documento: string | null;
    numero_documento: string | null;
    cuilt: string | null;
    email: string | null;
    telefono: string | null;
    vinculo: VinculoEncontrado | null;
    otros_alumnos: Array<{ id: number; nombre: string; legajo: string }>;
};

type Formulario = { responsable_id: number | ''; vinculo: DatosVinculo };

function desdeVinculado(
    responsable: ResponsableVinculado,
): ResponsableEncontrado {
    return {
        ...responsable,
        cuilt: null,
        vinculo: {
            id: responsable.alumno_responsable_id,
            activo: responsable.vinculo_activo,
            vinculo_id: responsable.tipo_vinculo_id,
            fecha_desde: responsable.fecha_desde,
            fecha_hasta: responsable.fecha_hasta,
            es_responsable_legal: responsable.es_responsable_legal,
            es_responsable_pedagogico: responsable.es_responsable_pedagogico,
            es_responsable_economico: responsable.es_responsable_economico,
            recibe_comunicaciones: responsable.recibe_comunicaciones,
            recibe_informes_academicos: responsable.recibe_informes_academicos,
            recibe_facturacion: responsable.recibe_facturacion,
            es_contacto_principal: responsable.es_contacto_principal,
            es_contacto_emergencia: responsable.es_contacto_emergencia,
            convive_con_alumno: responsable.convive_con_alumno,
            autorizado_retirar: responsable.autorizado_retirar,
            habilitado_portal: responsable.habilitado_portal,
            observaciones: responsable.observaciones,
        },
        otros_alumnos: [],
    };
}

export default function Responsables({
    alumno,
    responsables,
    tiposVinculo,
}: {
    alumno: AlumnoResumen;
    responsables: ResponsableVinculado[];
    tiposVinculo: TipoVinculo[];
}) {
    const [dialogoAbierto, setDialogoAbierto] = useState(false);
    const [paso, setPaso] = useState<'buscar' | 'vinculo'>('buscar');
    const [resultados, setResultados] = useState<ResponsableEncontrado[]>([]);
    const [seleccionado, setSeleccionado] =
        useState<ResponsableEncontrado | null>(null);
    const busqueda = useHttp<
        { buscar: string },
        { responsables: ResponsableEncontrado[] }
    >({
        buscar: '',
    });
    const form = useForm<Formulario>({
        responsable_id: '',
        vinculo: vinculoVacio(),
    });

    const abrirBusqueda = () => {
        setPaso('buscar');
        setSeleccionado(null);
        setResultados([]);
        busqueda.resetAndClearErrors();
        form.resetAndClearErrors();
        setDialogoAbierto(true);
    };

    const elegir = (responsable: ResponsableEncontrado) => {
        const previo = responsable.vinculo;
        const reactivando = previo !== null && !previo.activo;
        const vinculo: DatosVinculo = previo
            ? {
                  vinculo_id: previo.vinculo_id,
                  fecha_desde: reactivando
                      ? toDateOnlyString(new Date())
                      : (previo.fecha_desde ?? ''),
                  fecha_hasta: reactivando ? '' : (previo.fecha_hasta ?? ''),
                  es_responsable_legal: previo.es_responsable_legal,
                  es_responsable_pedagogico: previo.es_responsable_pedagogico,
                  es_responsable_economico: previo.es_responsable_economico,
                  recibe_comunicaciones: previo.recibe_comunicaciones,
                  recibe_informes_academicos: previo.recibe_informes_academicos,
                  recibe_facturacion: previo.recibe_facturacion,
                  es_contacto_principal: previo.es_contacto_principal,
                  es_contacto_emergencia: previo.es_contacto_emergencia,
                  convive_con_alumno: previo.convive_con_alumno,
                  autorizado_retirar: previo.autorizado_retirar,
                  habilitado_portal: previo.habilitado_portal,
                  observaciones: previo.observaciones ?? '',
              }
            : { ...vinculoVacio(), fecha_desde: toDateOnlyString(new Date()) };

        setSeleccionado(responsable);
        form.setData({ responsable_id: responsable.id, vinculo });
        form.clearErrors();
        setPaso('vinculo');
    };

    const buscar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        void busqueda
            .get(AlumnoResponsableController.buscar.url(alumno.id), {
                onSuccess: (respuesta) => setResultados(respuesta.responsables),
            })
            .catch(() => undefined);
    };

    const guardarVinculo = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.submit(AlumnoResponsableController.store(alumno.id), {
            preserveScroll: true,
            onSuccess: () => setDialogoAbierto(false),
        });
    };

    const columnas: ColumnDef<ResponsableVinculado>[] = [
        {
            id: 'id',
            header: '#',
            accessorFn: (responsable) => responsable.id,
            meta: { className: 'text-center tabular-nums' },
            cell: ({ row }) => formatId(row.original.id, 6),
        },
        {
            id: 'responsable',
            header: 'Responsable',
            accessorFn: (responsable) =>
                `${responsable.apellido}, ${responsable.nombre} ${responsable.nombre_elegido ?? ''}`,
            cell: ({ row }) => (
                <div>
                    <p className="font-medium text-foreground">
                        {row.original.apellido}, {row.original.nombre}
                    </p>
                    <p className="text-xs text-muted-foreground">
                        {[
                            row.original.tipo_documento,
                            row.original.numero_documento,
                        ]
                            .filter(Boolean)
                            .join(' ') || 'Sin documento informado'}
                    </p>
                </div>
            ),
        },
        {
            id: 'vinculo',
            header: 'Vínculo',
            accessorFn: (responsable) => responsable.tipo_vinculo,
            cell: ({ row }) => (
                <div>
                    <p>{row.original.tipo_vinculo}</p>
                    {(row.original.fecha_desde || row.original.fecha_hasta) && (
                        <p className="text-xs text-muted-foreground">
                            {formatDate(row.original.fecha_desde, 'Sin inicio')}{' '}
                            –{' '}
                            {formatDate(row.original.fecha_hasta, 'actualidad')}
                        </p>
                    )}
                </div>
            ),
        },
        {
            id: 'contacto',
            header: 'Contacto',
            accessorFn: (responsable) =>
                `${responsable.telefono ?? ''} ${responsable.email ?? ''}`,
            meta: { className: 'hidden lg:table-cell' },
            cell: ({ row }) => (
                <div>
                    <p>{row.original.telefono ?? 'Sin teléfono'}</p>
                    <p className="text-xs text-muted-foreground">
                        {row.original.email ?? 'Sin email'}
                    </p>
                </div>
            ),
        },
        {
            id: 'estado',
            header: 'Estado',
            accessorFn: (responsable) =>
                responsable.activo && responsable.vinculo_activo
                    ? 'Activo'
                    : 'Inactivo',
            meta: { className: 'text-center' },
            cell: ({ row }) => {
                const activo =
                    row.original.activo && row.original.vinculo_activo;

                return (
                    <Badge
                        variant="outline"
                        className={
                            activo
                                ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                : 'border-eure-error/30 bg-eure-error/10 text-eure-error'
                        }
                    >
                        {activo ? 'Activo' : 'Inactivo'}
                    </Badge>
                );
            },
        },
        {
            id: 'acciones',
            header: '',
            enableSorting: false,
            meta: { className: 'w-12 text-center' },
            cell: ({ row }) => (
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    title="Editar vínculo"
                    onClick={() => {
                        setDialogoAbierto(true);
                        elegir(desdeVinculado(row.original));
                    }}
                >
                    <PencilIcon className="size-4" />
                    <span className="sr-only">Editar vínculo</span>
                </Button>
            ),
        },
    ];

    return (
        <EducatioCard>
            <div className="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h2 className="flex items-center gap-2 font-semibold text-foreground">
                        <UsersRoundIcon
                            className="size-5 text-educatio-icon"
                            aria-hidden="true"
                        />
                        Responsables vinculados
                    </h2>
                    <p className="mt-0.5 text-sm text-muted-foreground">
                        Personas responsables y funciones asociadas a este
                        alumno.
                    </p>
                </div>
                <EureButtonPrimary type="button" onClick={abrirBusqueda}>
                    <LinkIcon className="size-4" />
                    Vincular responsable
                </EureButtonPrimary>
            </div>

            {responsables.length === 0 ? (
                <div className="flex min-h-52 flex-col items-center justify-center rounded-lg border border-dashed border-border bg-muted/20 px-6 text-center">
                    <UsersRoundIcon className="size-9 text-muted-foreground" />
                    <p className="mt-3 font-medium text-foreground">
                        El alumno no tiene responsables vinculados
                    </p>
                    <p className="mt-1 max-w-md text-sm text-muted-foreground">
                        Buscá una persona existente o cargá una nueva.
                    </p>
                    <EureButtonSecondary
                        type="button"
                        className="mt-4"
                        onClick={abrirBusqueda}
                    >
                        <LinkIcon className="size-4" />
                        Vincular responsable
                    </EureButtonSecondary>
                </div>
            ) : (
                <EureDataTable
                    variant="plain"
                    columns={columnas}
                    data={responsables}
                    showExportButtons={false}
                    showGlobalFilter={false}
                    disablePagination
                    getRowClassName={(responsable) =>
                        responsable.activo && responsable.vinculo_activo
                            ? ''
                            : 'opacity-65'
                    }
                />
            )}

            <Dialog
                open={dialogoAbierto}
                onOpenChange={(abierto) => {
                    if (!abierto && !form.processing && !busqueda.processing) {
                        setDialogoAbierto(false);
                    }
                }}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-5xl">
                    <DialogHeader>
                        <DialogTitle>
                            {paso === 'buscar'
                                ? 'Vincular responsable'
                                : `${seleccionado?.apellido}, ${seleccionado?.nombre}`}
                        </DialogTitle>
                        <DialogDescription>
                            {paso === 'buscar'
                                ? 'Primero buscá si la persona ya está cargada.'
                                : 'Configurá el vínculo y las funciones para este alumno.'}
                        </DialogDescription>
                    </DialogHeader>

                    {paso === 'buscar' ? (
                        <div className="space-y-4">
                            <form
                                onSubmit={buscar}
                                className="flex flex-col gap-3 sm:flex-row sm:items-end"
                            >
                                <EureInputGroup
                                    className="flex-1"
                                    label="Apellido, documento, CUIL/T, email o teléfono"
                                    name="buscar_responsable"
                                    value={busqueda.data.buscar}
                                    onChange={(event) =>
                                        busqueda.setData(
                                            'buscar',
                                            event.target.value,
                                        )
                                    }
                                    error={Boolean(busqueda.errors.buscar)}
                                    errorMessage={busqueda.errors.buscar}
                                    maxLength={100}
                                    autoFocus
                                />
                                <EureButtonPrimary
                                    type="submit"
                                    loading={busqueda.processing}
                                >
                                    <SearchIcon className="size-4" />
                                    Buscar
                                </EureButtonPrimary>
                            </form>

                            <div className="flex justify-end">
                                <EureButtonSecondary asChild>
                                    <Link
                                        href={ResponsableController.create(
                                            alumno.id,
                                        )}
                                    >
                                        <PlusIcon className="size-4" />
                                        Crear nuevo responsable
                                    </Link>
                                </EureButtonSecondary>
                            </div>

                            {resultados.length > 0 ? (
                                <div className="overflow-x-auto rounded-md border border-border">
                                    <table className="w-full text-sm">
                                        <thead className="bg-muted/60 text-left">
                                            <tr>
                                                <th className="px-3 py-2 text-center">
                                                    #
                                                </th>
                                                <th className="px-3 py-2">
                                                    Responsable
                                                </th>
                                                <th className="px-3 py-2">
                                                    Documento / CUIL/T
                                                </th>
                                                <th className="px-3 py-2">
                                                    Contacto
                                                </th>
                                                <th className="px-3 py-2">
                                                    Estado
                                                </th>
                                                <th className="px-3 py-2" />
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {resultados.map((responsable) => (
                                                <tr key={responsable.id}>
                                                    <td className="px-3 py-3 text-center tabular-nums">
                                                        {formatId(
                                                            responsable.id,
                                                            6,
                                                        )}
                                                    </td>
                                                    <td className="px-3 py-3">
                                                        <p className="font-medium">
                                                            {
                                                                responsable.apellido
                                                            }
                                                            ,{' '}
                                                            {responsable.nombre}
                                                        </p>
                                                        {responsable
                                                            .otros_alumnos
                                                            .length > 0 && (
                                                            <p className="text-xs text-muted-foreground">
                                                                También
                                                                vinculado a{' '}
                                                                {responsable.otros_alumnos
                                                                    .map(
                                                                        (
                                                                            otro,
                                                                        ) =>
                                                                            otro.nombre,
                                                                    )
                                                                    .join(', ')}
                                                            </p>
                                                        )}
                                                    </td>
                                                    <td className="px-3 py-3">
                                                        <p>
                                                            {[
                                                                responsable.tipo_documento,
                                                                responsable.numero_documento,
                                                            ]
                                                                .filter(Boolean)
                                                                .join(' ') ||
                                                                '—'}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {responsable.cuilt ??
                                                                'Sin CUIL/T'}
                                                        </p>
                                                    </td>
                                                    <td className="px-3 py-3">
                                                        <p>
                                                            {responsable.telefono ??
                                                                '—'}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {responsable.email ??
                                                                '—'}
                                                        </p>
                                                    </td>
                                                    <td className="px-3 py-3">
                                                        <Badge variant="outline">
                                                            {!responsable.activo
                                                                ? 'Persona inactiva'
                                                                : responsable
                                                                        .vinculo
                                                                        ?.activo
                                                                  ? 'Ya vinculado'
                                                                  : responsable.vinculo
                                                                    ? 'Vínculo histórico'
                                                                    : 'Disponible'}
                                                        </Badge>
                                                    </td>
                                                    <td className="px-3 py-3 text-right">
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() =>
                                                                elegir(
                                                                    responsable,
                                                                )
                                                            }
                                                        >
                                                            {responsable.vinculo
                                                                ?.activo
                                                                ? 'Editar'
                                                                : responsable.vinculo
                                                                  ? 'Reactivar'
                                                                  : 'Vincular'}
                                                        </Button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : busqueda.wasSuccessful ? (
                                <p className="rounded-md border border-dashed border-border p-6 text-center text-sm text-muted-foreground">
                                    No encontramos responsables con ese
                                    criterio.
                                </p>
                            ) : null}
                        </div>
                    ) : (
                        <form onSubmit={guardarVinculo} className="space-y-5">
                            {seleccionado && !seleccionado.activo && (
                                <p className="rounded-md border border-educatio-warning/30 bg-educatio-warning/10 px-4 py-3 text-sm text-educatio-warning">
                                    La persona está inactiva y se reactivará al
                                    guardar.
                                </p>
                            )}
                            <FormularioVinculo
                                value={form.data.vinculo}
                                onChange={(vinculo) =>
                                    form.setData('vinculo', vinculo)
                                }
                                tiposVinculo={tiposVinculo}
                                errors={
                                    form.errors as unknown as Record<
                                        string,
                                        string | undefined
                                    >
                                }
                            />
                            <DialogFooter>
                                <EureButtonSecondary
                                    type="button"
                                    onClick={() => setPaso('buscar')}
                                    disabled={form.processing}
                                >
                                    Volver a la búsqueda
                                </EureButtonSecondary>
                                <EureButtonPrimary
                                    type="submit"
                                    loading={form.processing}
                                >
                                    Guardar vínculo
                                </EureButtonPrimary>
                            </DialogFooter>
                        </form>
                    )}
                </DialogContent>
            </Dialog>
        </EducatioCard>
    );
}
