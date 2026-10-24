import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { EyeIcon, ListFilterIcon, PencilIcon, UserCogIcon } from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo, useState } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';
import { formatCuilt } from '@/lib/cuit';
import { show as alumnoShow } from '@/routes/academico/alumnos';
import { edit, index, show } from '@/routes/academico/responsables';

import type { ResponsableResumen as Responsable } from './types';

type Filtros = {
    buscar: string;
    provincia_id: string;
    ciudad_id: string;
    solo_activos: boolean;
};

type Resultados = {
    total: number;
    filtrados: number;
};

type CiudadFiltro = EureSelectOption & {
    provincia_id: number;
};

const RESPONSABLE_FILTER_KEYS = [
    'buscar',
    'provincia_id',
    'ciudad_id',
    'solo_activos',
] as const satisfies readonly (keyof Filtros)[];

function normalizeFilterValue(
    key: keyof Filtros,
    value: Filtros[keyof Filtros],
): string | boolean {
    return key === 'buscar' && typeof value === 'string' ? value.trim() : value;
}

const columnas: ColumnDef<Responsable>[] = [
    {
        id: 'id',
        header: '#',
        accessorFn: (responsable) => responsable.id,
        meta: { className: 'text-center tabular-nums' },
    },
    {
        id: 'responsable',
        header: 'Responsable',
        accessorFn: (responsable) =>
            `${responsable.apellido}, ${responsable.nombre} ${responsable.nombre_elegido ?? ''}`,
        cell: ({ row: { original: responsable } }) => (
            <div className="min-w-40">
                <span className="font-medium">
                    {responsable.apellido}, {responsable.nombre}
                </span>
                {responsable.nombre_elegido && (
                    <span className="block text-xs text-muted-foreground">
                        {responsable.nombre_elegido}
                    </span>
                )}
            </div>
        ),
    },
    {
        id: 'documento',
        header: 'Documento',
        accessorFn: (responsable) =>
            `${responsable.tipo_documento?.nombre ?? ''} ${responsable.numero_documento ?? ''}`.trim(),
        cell: ({ getValue }) => getValue<string>() || '—',
        meta: { className: 'text-center' },
    },
    {
        id: 'cuilt',
        header: 'CUIL/T',
        accessorFn: (responsable) => responsable.cuilt ?? '',
        cell: ({ row }) => formatCuilt(row.original.cuilt),
        meta: { className: 'text-center tabular-nums' },
    },
    {
        id: 'contacto',
        header: 'Contacto',
        accessorFn: (responsable) =>
            `${responsable.email ?? ''} ${responsable.telefono_e164 ?? ''}`.trim(),
        cell: ({ row: { original: responsable } }) => (
            <div className="max-w-56 space-y-1 break-words">
                {responsable.email && <div>{responsable.email}</div>}
                {responsable.telefono_e164 && (
                    <div className="whitespace-nowrap tabular-nums">
                        {responsable.telefono_e164}
                    </div>
                )}
                {!responsable.email && !responsable.telefono_e164 && '—'}
            </div>
        ),
    },
    {
        id: 'alumnos',
        header: 'Alumnos vinculados',
        accessorFn: (responsable) =>
            responsable.alumnos_responsables
                .map(
                    ({ alumno }) =>
                        `${alumno.apellido}, ${alumno.nombre} (${alumno.legajo})`,
                )
                .join(' / '),
        cell: ({ row }) => (
            <div className="min-w-40 space-y-2">
                {row.original.alumnos_responsables.length === 0
                    ? 'Sin alumnos vinculados'
                    : row.original.alumnos_responsables.map((vinculo) => (
                          <div key={vinculo.id}>
                              <Link
                                  href={alumnoShow(vinculo.alumno.id, {
                                      query: { seccion: 'responsables' },
                                  })}
                                  className="rounded-sm font-medium text-primary underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                              >
                                  {vinculo.alumno.apellido},{' '}
                                  {vinculo.alumno.nombre}
                              </Link>
                              <span className="block text-xs text-muted-foreground">
                                  Legajo {vinculo.alumno.legajo}
                                  {!vinculo.activo && ' · Vínculo inactivo'}
                                  {!vinculo.alumno.activo &&
                                      ' · Alumno inactivo'}
                              </span>
                          </div>
                      ))}
            </div>
        ),
    },
    {
        id: 'estado',
        header: 'Estado',
        accessorFn: (responsable) =>
            responsable.activo ? 'Activo' : 'Inactivo',
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
        meta: { className: 'text-center' },
    },
];

export default function ResponsablesIndex({
    responsables,
    filters,
    resultados,
    provincias,
    ciudades,
}: {
    responsables: Responsable[];
    filters: Filtros;
    resultados: Resultados;
    provincias: EureSelectOption[];
    ciudades: CiudadFiltro[];
}) {
    const [isFiltering, setIsFiltering] = useState(false);
    const [filtros, setFiltros] = useState<Filtros>(filters);

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

        router.get(index.url(), buildQuery(), {
            only: ['responsables', 'filters', 'resultados'],
            preserveState: true,
            replace: true,
            onStart: () => setIsFiltering(true),
            onFinish: () => setIsFiltering(false),
        });
    };

    const filtrosPendientes = useMemo(
        () =>
            RESPONSABLE_FILTER_KEYS.some(
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

    return (
        <>
            <Head title="Responsables" />
            <div className="flex min-w-0 flex-1 flex-col gap-4">
                <EducatioCard>
                    <header className="flex items-center gap-3">
                        <UserCogIcon
                            className="size-8 shrink-0 text-educatio-icon"
                            aria-hidden="true"
                        />
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Responsables
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Padres, madres y tutores. Accedé a un alumno
                                para gestionar sus vínculos.
                            </p>
                        </div>
                    </header>
                </EducatioCard>

                <div className="grid gap-4 2xl:grid-cols-[17rem_minmax(0,1fr)] 2xl:items-start">
                    <EducatioCard className="2xl:sticky 2xl:top-24">
                        <form
                            onSubmit={buscar}
                            className="grid grid-cols-1 gap-3 md:grid-cols-12 2xl:grid-cols-1"
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
                                placeholder="Responsable, documento, CUIL/T, contacto o alumno"
                                maxLength={100}
                                autoFocus
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
                                            <span className="text-educatio-warning not-italic">
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
                                        href={index.url()}
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
                    </EducatioCard>

                    <EducatioCard className="min-w-0">
                        <EureDataTable
                            variant="plain"
                            columns={columnas}
                            data={responsables}
                            globalFilterPlaceholder={
                                hayFiltrosAplicados
                                    ? 'Buscar en resultados...'
                                    : 'Buscar...'
                            }
                            showExportButtons={false}
                            initialPageSize={10}
                            getColumnClassName={(id) =>
                                id === 'cuilt' ? 'hidden xl:table-cell' : ''
                            }
                            getRowClassName={(responsable) =>
                                responsable.activo ? '' : 'opacity-65'
                            }
                            acciones={[
                                {
                                    label: 'Ver',
                                    icon: EyeIcon,
                                    onClick: (responsable) =>
                                        router.visit(show(responsable.id)),
                                },
                                {
                                    label: 'Editar',
                                    icon: PencilIcon,
                                    onClick: (responsable) =>
                                        router.visit(edit(responsable.id)),
                                },
                            ]}
                        />
                        {responsables.length === 0 && (
                            <p
                                className="text-sm text-muted-foreground"
                                role="status"
                            >
                                No hay responsables que coincidan con los
                                filtros.
                            </p>
                        )}
                    </EducatioCard>
                </div>
            </div>
        </>
    );
}

ResponsablesIndex.layout = {
    breadcrumbs: [{ title: 'Responsables', href: index.url() }],
};
