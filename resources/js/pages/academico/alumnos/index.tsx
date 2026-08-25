import { Head, Link, router, useForm } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    EyeIcon,
    ListFilterIcon,
    PencilIcon,
    PlusIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo, useState } from 'react';
import AlumnoController from '@/actions/App/Http/Controllers/Academico/AlumnoController';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
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
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureDatePickerGroup from '@/components/ui/eure/EureDatePickerGroup';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';
import { formatDate, parseDateOnly, toDateOnlyString } from '@/lib/date';
import { formatId } from '@/lib/utils';

type TipoDocumento = {
    id: number;
    nombre: string;
};

type Ciudad = {
    id: number;
    nombre: string;
};

type Alumno = {
    id: number;
    activo: boolean;
    legajo: string;
    apellido: string;
    nombre: string;
    nombre_elegido: string | null;
    tipo_documento_id: number | null;
    numero_documento: string | null;
    fecha_nacimiento: string | null;
    ciudad_id: number | null;
    fecha_baja: string | null;
    tipo_documento: TipoDocumento | null;
    ciudad: Ciudad | null;
};

type MotivoBaja = {
    id: number;
    nombre: string;
};

type AlumnoFilters = {
    buscar: string;
    provincia_id: string;
    ciudad_id: string;
    solo_activos: boolean;
};

type AlumnoResultados = {
    total: number;
    filtrados: number;
};

type CiudadFiltro = EureSelectOption & {
    provincia_id: number;
};

type DatosBaja = {
    fecha_baja: string;
    motivo_baja_id: number | '';
};

type Props = {
    alumnos: Alumno[];
    filters: AlumnoFilters;
    resultados: AlumnoResultados;
    provincias: EureSelectOption[];
    ciudades: CiudadFiltro[];
    motivosBaja: MotivoBaja[];
};

const ALUMNO_FILTER_KEYS = [
    'buscar',
    'provincia_id',
    'ciudad_id',
    'solo_activos',
] as const satisfies readonly (keyof AlumnoFilters)[];

function normalizeFilterValue(
    key: keyof AlumnoFilters,
    value: AlumnoFilters[keyof AlumnoFilters],
): string | boolean {
    return key === 'buscar' && typeof value === 'string' ? value.trim() : value;
}

export default function AlumnosIndex({
    alumnos,
    filters,
    resultados,
    provincias,
    ciudades,
    motivosBaja,
}: Props) {
    const [alumnoBaja, setAlumnoBaja] = useState<Alumno | null>(null);
    const [cambiandoEstado, setCambiandoEstado] = useState<number | null>(null);
    const [isFiltering, setIsFiltering] = useState(false);
    const [filtros, setFiltros] = useState<AlumnoFilters>(filters);
    const bajaForm = useForm<DatosBaja>({
        fecha_baja: toDateOnlyString(new Date()),
        motivo_baja_id: '',
    });

    const opcionesProvincia = useMemo(
        () => [{ label: 'Todas', value: '' }, ...provincias],
        [provincias],
    );

    const opcionesCiudad = useMemo(() => {
        const provinciaId = filtros.provincia_id
            ? Number(filtros.provincia_id)
            : null;
        const disponibles = provinciaId
            ? ciudades.filter(
                  (ciudad) =>
                      ciudad.provincia_id === provinciaId ||
                      String(ciudad.value) === filtros.ciudad_id,
              )
            : ciudades;

        return [{ label: 'Todas', value: '' }, ...disponibles];
    }, [ciudades, filtros.ciudad_id, filtros.provincia_id]);

    const provinciaSeleccionada =
        opcionesProvincia.find(
            (opcion) => String(opcion.value) === filtros.provincia_id,
        ) ?? opcionesProvincia[0];

    const ciudadSeleccionada =
        opcionesCiudad.find(
            (opcion) => String(opcion.value) === filtros.ciudad_id,
        ) ?? opcionesCiudad[0];

    const provinciaFiltrada = provincias.find(
        (opcion) => String(opcion.value) === filters.provincia_id,
    );

    const ciudadFiltrada = ciudades.find(
        (opcion) => String(opcion.value) === filters.ciudad_id,
    );

    const buildQuery = (): Record<string, string> => {
        const query = {
            buscar: filtros.buscar.trim(),
            provincia_id: filtros.provincia_id,
            ciudad_id: filtros.ciudad_id,
            solo_activos: filtros.solo_activos ? '1' : '',
        };

        return Object.fromEntries(
            Object.entries(query).filter(([, value]) => value !== ''),
        );
    };

    const buscar = (event?: FormEvent<HTMLFormElement>) => {
        event?.preventDefault();

        router.get(AlumnoController.index().url, buildQuery(), {
            only: ['alumnos', 'filters', 'resultados'],
            preserveState: true,
            replace: true,
            onStart: () => setIsFiltering(true),
            onFinish: () => setIsFiltering(false),
        });
    };

    const filtrosPendientes = useMemo(
        () =>
            ALUMNO_FILTER_KEYS.some(
                (key) =>
                    normalizeFilterValue(key, filtros[key]) !==
                    normalizeFilterValue(key, filters[key]),
            ),
        [filtros, filters],
    );

    const hayFiltrosAplicados = useMemo(
        () =>
            filters.buscar.trim() !== '' ||
            filters.provincia_id !== '' ||
            filters.ciudad_id !== '' ||
            filters.solo_activos,
        [filters],
    );

    const resumenFiltros = useMemo(() => {
        const partes: string[] = [];

        if (filters.buscar.trim()) {
            partes.push(`texto “${filters.buscar.trim()}”`);
        }

        if (provinciaFiltrada?.value) {
            partes.push(`provincia ${provinciaFiltrada.label}`);
        }

        if (ciudadFiltrada?.value) {
            partes.push(`localidad ${ciudadFiltrada.label}`);
        }

        if (filters.solo_activos) {
            partes.push('solo activos');
        }

        const contador = `${resultados.filtrados} de ${resultados.total}`;

        return partes.length === 0
            ? `Sin filtros aplicados (${contador})`
            : `Filtrado por ${partes.join(', ')} (${contador})`;
    }, [
        ciudadFiltrada,
        filters,
        provinciaFiltrada,
        resultados.filtrados,
        resultados.total,
    ]);

    const opcionesMotivo = useMemo<EureSelectOption[]>(
        () =>
            motivosBaja.map((motivo) => ({
                value: motivo.id,
                label: motivo.nombre,
            })),
        [motivosBaja],
    );

    const motivoSeleccionado =
        opcionesMotivo.find(
            (opcion) => opcion.value === bajaForm.data.motivo_baja_id,
        ) ?? null;

    const abrirBaja = (alumno: Alumno) => {
        bajaForm.setData({
            fecha_baja: toDateOnlyString(new Date()),
            motivo_baja_id: '',
        });
        bajaForm.clearErrors();
        setAlumnoBaja(alumno);
    };

    const cerrarBaja = () => {
        if (!bajaForm.processing) {
            setAlumnoBaja(null);
            bajaForm.resetAndClearErrors();
        }
    };

    const desactivar = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (alumnoBaja === null) {
            return;
        }

        bajaForm.submit(AlumnoController.desactivar(alumnoBaja.id), {
            preserveScroll: true,
            onSuccess: cerrarBaja,
        });
    };

    const reactivar = async (alumno: Alumno) => {
        const confirmado = await EureConfirmSwal()({
            title: 'Reactivar alumno',
            text: `“${alumno.apellido}, ${alumno.nombre}” volverá a estar disponible.`,
            confirmText: 'Reactivar',
        });

        if (!confirmado) {
            return;
        }

        setCambiandoEstado(alumno.id);
        router.visit(AlumnoController.reactivar(alumno.id), {
            preserveScroll: true,
            onFinish: () => setCambiandoEstado(null),
        });
    };

    const columnas = useMemo<ColumnDef<Alumno>[]>(
        () => [
            {
                id: 'id',
                header: '#',
                accessorFn: (alumno) => alumno.id,
                meta: { className: 'text-center tabular-nums' },
                cell: ({ row }) => formatId(row.original.id, 6),
            },
            {
                id: 'legajo',
                header: 'Legajo',
                accessorFn: (alumno) => alumno.legajo,
                meta: { className: 'text-center' },
                cell: ({ getValue }) => (
                    <span className="font-mono text-xs font-semibold tracking-wide">
                        {String(getValue())}
                    </span>
                ),
            },
            {
                id: 'alumno',
                header: 'Alumno',
                accessorFn: (alumno) =>
                    `${alumno.apellido}, ${alumno.nombre} ${alumno.nombre_elegido ?? ''}`,
                cell: ({ row }) => (
                    <div>
                        <span className="font-medium">
                            {row.original.apellido}, {row.original.nombre}
                        </span>
                        {row.original.nombre_elegido && (
                            <span className="ml-1 text-xs text-muted-foreground">
                                ({row.original.nombre_elegido})
                            </span>
                        )}
                    </div>
                ),
            },
            {
                id: 'documento',
                header: 'Documento',
                accessorFn: (alumno) =>
                    alumno.numero_documento
                        ? `${alumno.tipo_documento?.nombre ?? ''} ${alumno.numero_documento}`
                        : '',
                meta: { className: 'text-center' },
                cell: ({ row }) =>
                    row.original.numero_documento
                        ? `${row.original.tipo_documento?.nombre ?? ''} ${row.original.numero_documento}`
                        : '—',
            },
            {
                id: 'fecha_nacimiento',
                header: 'Nacimiento',
                accessorFn: (alumno) => alumno.fecha_nacimiento ?? '',
                meta: { className: 'text-center tabular-nums' },
                cell: ({ row }) =>
                    formatDate(row.original.fecha_nacimiento, '—'),
            },
            {
                id: 'localidad',
                header: 'Localidad',
                accessorFn: (alumno) => alumno.ciudad?.nombre ?? '',
                cell: ({ row }) => row.original.ciudad?.nombre ?? '—',
            },
            {
                id: 'estado',
                header: 'Estado',
                accessorFn: (alumno) => (alumno.activo ? 'Activo' : 'Inactivo'),
                meta: { className: 'text-center' },
                cell: ({ row }) => (
                    <Badge
                        variant="outline"
                        className={
                            row.original.activo
                                ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                : 'border-eure-error/30 bg-eure-error/10 text-eure-error'
                        }
                    >
                        {row.original.activo ? 'Activo' : 'Inactivo'}
                    </Badge>
                ),
            },
        ],
        [],
    );

    return (
        <>
            <Head title="Alumnos" />

            <div className="flex flex-1 flex-col gap-5 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                            Alumnos
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Legajos y datos personales de los alumnos.
                        </p>
                    </div>

                    <EureButtonPrimary
                        onClick={() => router.visit(AlumnoController.create())}
                        className="shrink-0"
                    >
                        <PlusIcon className="size-4" />
                        Nuevo alumno
                    </EureButtonPrimary>
                </div>

                <div className="grid gap-4 2xl:grid-cols-[17rem_minmax(0,1fr)] 2xl:items-start">
                    <form
                        onSubmit={buscar}
                        className="grid grid-cols-1 gap-4 rounded-lg border border-border bg-card p-4 shadow-sm md:grid-cols-12 2xl:sticky 2xl:top-24 2xl:grid-cols-1 2xl:gap-3"
                    >
                        <EureInputGroup
                            label="Buscar (que contenga)"
                            name="buscar"
                            value={filtros.buscar}
                            onChange={(event) =>
                                setFiltros((actuales) => ({
                                    ...actuales,
                                    buscar: event.target.value,
                                }))
                            }
                            placeholder="Legajo, alumno, documento o CUIL/T"
                            className="md:col-span-4 2xl:col-span-1"
                        />

                        <EureSelectGroup
                            label="Provincia"
                            name="provincia_id"
                            value={provinciaSeleccionada}
                            options={opcionesProvincia}
                            onChange={(opcion) =>
                                setFiltros((actuales) => ({
                                    ...actuales,
                                    provincia_id: opcion?.value
                                        ? String(opcion.value)
                                        : '',
                                    ciudad_id: '',
                                }))
                            }
                            className="md:col-span-3 2xl:col-span-1"
                        />

                        <EureSelectGroup
                            label="Localidad"
                            name="ciudad_id"
                            value={ciudadSeleccionada}
                            options={opcionesCiudad}
                            onChange={(opcion) =>
                                setFiltros((actuales) => ({
                                    ...actuales,
                                    ciudad_id: opcion?.value
                                        ? String(opcion.value)
                                        : '',
                                }))
                            }
                            className="md:col-span-3 2xl:col-span-1"
                        />

                        <div className="flex items-end md:col-span-2 2xl:col-span-1">
                            <label
                                htmlFor="solo_activos"
                                className="flex cursor-pointer items-center gap-2 pb-2 text-sm font-semibold text-foreground"
                            >
                                <Checkbox
                                    id="solo_activos"
                                    checked={filtros.solo_activos}
                                    onCheckedChange={(checked) =>
                                        setFiltros((actuales) => ({
                                            ...actuales,
                                            solo_activos: checked === true,
                                        }))
                                    }
                                />
                                Solo activos
                            </label>
                        </div>

                        <div className="flex flex-col gap-3 md:col-span-12 md:flex-row md:items-end 2xl:col-span-1 2xl:flex-col 2xl:items-stretch">
                            <p className="min-w-0 flex-1 text-sm text-muted-foreground italic 2xl:w-full 2xl:flex-none">
                                {resumenFiltros}
                                {filtrosPendientes && (
                                    <>
                                        <span className="text-amber-700 not-italic dark:text-amber-300">
                                            {' '}
                                            · Cambios sin aplicar ·{' '}
                                        </span>
                                        <button
                                            type="submit"
                                            className="font-medium text-primary not-italic underline underline-offset-4"
                                        >
                                            Actualizar
                                        </button>
                                    </>
                                )}
                            </p>

                            <div className="flex flex-wrap items-end gap-2 md:justify-end 2xl:w-full 2xl:flex-col 2xl:items-stretch">
                                <Link
                                    href={AlumnoController.index().url}
                                    className="text-sm font-medium text-primary underline underline-offset-4 2xl:block 2xl:w-full 2xl:text-right"
                                >
                                    Limpiar filtros
                                </Link>

                                <EureButtonPrimary
                                    type="submit"
                                    loading={isFiltering}
                                    loadingPosition="right"
                                    className="2xl:w-full 2xl:justify-center 2xl:self-stretch"
                                >
                                    <ListFilterIcon className="size-4" />
                                    Filtrar
                                </EureButtonPrimary>
                            </div>
                        </div>
                    </form>

                    <div className="min-w-0">
                        <EureDataTable
                            columns={columnas}
                            data={alumnos}
                            globalFilterPlaceholder={
                                hayFiltrosAplicados
                                    ? 'Buscar en resultados...'
                                    : 'Buscar...'
                            }
                            showExportButtons={false}
                            initialPageSize={10}
                            getRowClassName={(alumno) =>
                                alumno.activo ? '' : 'opacity-65'
                            }
                            acciones={[
                                {
                                    label: 'Ver',
                                    icon: EyeIcon,
                                    color: 'blue',
                                    onClick: () => undefined,
                                },
                                {
                                    label: 'Editar',
                                    icon: PencilIcon,
                                    onClick: (alumno) =>
                                        router.visit(
                                            AlumnoController.edit(alumno.id),
                                        ),
                                },
                                {
                                    label: 'Desactivar',
                                    icon: Trash2Icon,
                                    color: 'red',
                                    hidden: (alumno) =>
                                        !alumno.activo ||
                                        cambiandoEstado === alumno.id,
                                    onClick: abrirBaja,
                                },
                                {
                                    label: 'Reactivar',
                                    icon: RotateCcwIcon,
                                    color: 'green',
                                    hidden: (alumno) =>
                                        alumno.activo ||
                                        cambiandoEstado === alumno.id,
                                    onClick: (alumno) => void reactivar(alumno),
                                },
                            ]}
                        />
                    </div>
                </div>
            </div>

            <Dialog
                open={alumnoBaja !== null}
                onOpenChange={(abierto) => {
                    if (!abierto) {
                        cerrarBaja();
                    }
                }}
            >
                <DialogContent className="sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Desactivar alumno</DialogTitle>
                        <DialogDescription>
                            Indicá cuándo y por qué se da de baja a{' '}
                            <strong>
                                {alumnoBaja?.apellido}, {alumnoBaja?.nombre}
                            </strong>
                            .
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={desactivar} className="space-y-4">
                        <EureDatePickerGroup
                            label="Fecha de baja"
                            name="fecha_baja"
                            value={parseDateOnly(bajaForm.data.fecha_baja)}
                            onChange={(fecha) =>
                                bajaForm.setData(
                                    'fecha_baja',
                                    toDateOnlyString(fecha),
                                )
                            }
                            maxDate={new Date()}
                            error={Boolean(bajaForm.errors.fecha_baja)}
                            errorMessage={bajaForm.errors.fecha_baja}
                            required
                        />

                        <EureSelectGroup
                            label="Motivo de baja"
                            name="motivo_baja_id"
                            value={motivoSeleccionado}
                            onChange={(opcion) =>
                                bajaForm.setData(
                                    'motivo_baja_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesMotivo}
                            placeholder="Buscar y seleccionar un motivo"
                            error={Boolean(bajaForm.errors.motivo_baja_id)}
                            errorMessage={bajaForm.errors.motivo_baja_id}
                            required
                        />

                        <DialogFooter className="pt-2">
                            <EureButtonSecondary
                                onClick={cerrarBaja}
                                disabled={bajaForm.processing}
                            >
                                Cancelar
                            </EureButtonSecondary>
                            <EureButtonPrimary
                                type="submit"
                                loading={bajaForm.processing}
                            >
                                Desactivar
                            </EureButtonPrimary>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

AlumnosIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Alumnos',
            href: AlumnoController.index().url,
        },
    ],
});
