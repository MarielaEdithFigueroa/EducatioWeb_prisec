import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    EyeIcon,
    LayersIcon,
    ListFilterIcon,
    PencilIcon,
    PlusIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import { formatId } from '@/lib/utils';
import { create, edit, index, show } from '@/routes/academico/grupos';
import { CAMPOS_GRUPO, consultaFiltros, nombreCatalogo } from './types';
import type { CatalogosGrupo, FiltrosGrupo, Grupo } from './types';
import { useEstadoGrupo } from './use-estado-grupo';

type Props = {
    grupos: Grupo[];
    catalogos: CatalogosGrupo;
    filters: FiltrosGrupo;
    resultados: { total: number; filtrados: number };
    ciclosLectivos: number[];
};

type GrupoFila = Grupo & { idF: string };

function columnasGrupo(filters: FiltrosGrupo): ColumnDef<GrupoFila>[] {
    return [
        {
            id: 'id',
            header: '#',
            accessorKey: 'id',
            meta: { className: 'text-center tabular-nums' },
            cell: ({ row }) => (
                <Link
                    href={show(row.original.id, {
                        query: { filtros: consultaFiltros(filters) },
                    })}
                    className="rounded-sm font-bold text-primary underline underline-offset-4 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    aria-label={`Ver grupo ${row.original.idF}`}
                >
                    {row.original.idF}
                </Link>
            ),
        },
        {
            id: 'ciclo',
            header: 'Ciclo',
            accessorFn: (grupo) => grupo.ciclo_lectivo,
            meta: { className: 'text-center tabular-nums' },
        },
        {
            id: 'nivel',
            header: 'Nivel',
            accessorFn: (grupo) => grupo.nivel.descripcion,
            meta: { className: 'text-left' },
        },
        {
            id: 'curso',
            header: 'Curso / división',
            accessorFn: (grupo) =>
                `${grupo.curso.descripcion} ${grupo.division.descripcion}`,
            meta: { className: 'text-left' },
        },
        {
            id: 'turno',
            header: 'Turno',
            accessorFn: (grupo) => grupo.turno.descripcion,
            meta: { className: 'text-left' },
        },
        {
            id: 'plan',
            header: 'Plan de estudio',
            accessorFn: (grupo) => nombreCatalogo(grupo.plan_estudio),
            meta: { className: 'text-left' },
        },
        {
            id: 'estado',
            header: 'Estado',
            accessorFn: (grupo) => (grupo.activo ? 'Activo' : 'Inactivo'),
            meta: { className: 'text-center' },
            cell: ({ row }) => (
                <Badge variant="outline">
                    {row.original.activo ? 'Activo' : 'Inactivo'}
                </Badge>
            ),
        },
    ];
}

export default function GruposIndex({
    grupos,
    catalogos,
    filters,
    resultados,
    ciclosLectivos,
}: Props) {
    const filas = useMemo<GrupoFila[]>(
        () => grupos.map((grupo) => ({ ...grupo, idF: formatId(grupo.id, 4) })),
        [grupos],
    );
    const columnas = useMemo(() => columnasGrupo(filters), [filters]);
    const [filtros, setFiltros] = useState(filters);
    const [filtrando, setFiltrando] = useState(false);
    const { procesando, cambiarEstado } = useEstadoGrupo();
    const pendientes = Object.keys(filters).some(
        (clave) =>
            filtros[clave as keyof FiltrosGrupo] !==
            filters[clave as keyof FiltrosGrupo],
    );
    const contexto = { query: { filtros: consultaFiltros(filters) } };
    const opcionesCiclo = [
        { value: '', label: 'Todos' },
        ...ciclosLectivos.map((ciclo) => ({
            value: String(ciclo),
            label: String(ciclo),
        })),
    ];
    const estados = [
        { value: '', label: 'Todos' },
        { value: 'activos', label: 'Activos' },
        { value: 'inactivos', label: 'Inactivos' },
    ];

    const buscar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get(index.url(), consultaFiltros(filtros), {
            only: ['grupos', 'filters', 'resultados', 'ciclosLectivos'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setFiltrando(true),
            onSuccess: (page) => setFiltros(page.props.filters as FiltrosGrupo),
            onFinish: () => setFiltrando(false),
        });
    };

    return (
        <>
            <Head title="Grupos" />
            <div className="flex flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                            <LayersIcon className="size-7 text-educatio-icon" />
                            Grupos
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Organización por ciclo lectivo, nivel, curso,
                            división y turno.
                        </p>
                    </div>
                    <EureButtonPrimary
                        onClick={() => router.visit(create(contexto))}
                    >
                        <PlusIcon className="size-4" />
                        Nuevo grupo
                    </EureButtonPrimary>
                </EducatioCard>
                <div className="grid gap-4 2xl:grid-cols-[17rem_minmax(0,1fr)] 2xl:items-start">
                    <EducatioCard className="2xl:sticky 2xl:top-24">
                        <form
                            onSubmit={buscar}
                            className="grid grid-cols-2 gap-3 md:grid-cols-4 2xl:grid-cols-1"
                        >
                            <EureSelectGroup
                                label="Ciclo lectivo"
                                name="filtro_ciclo"
                                options={opcionesCiclo}
                                value={
                                    opcionesCiclo.find(
                                        (opcion) =>
                                            opcion.value ===
                                            filtros.ciclo_lectivo,
                                    ) ?? {
                                        value: filtros.ciclo_lectivo,
                                        label: filtros.ciclo_lectivo,
                                    }
                                }
                                onChange={(opcion) =>
                                    setFiltros({
                                        ...filtros,
                                        ciclo_lectivo: String(
                                            opcion?.value ?? '',
                                        ),
                                    })
                                }
                            />
                            {CAMPOS_GRUPO.map(({ campo, catalogo, label }) => {
                                const opciones = [
                                    { value: '', label: 'Todos' },
                                    ...catalogos[catalogo]
                                        .filter(
                                            (registro) =>
                                                !registro.nivel_id ||
                                                !filtros.nivel_id ||
                                                String(registro.nivel_id) ===
                                                    filtros.nivel_id ||
                                                String(registro.id) ===
                                                    filtros[campo],
                                        )
                                        .map((registro) => ({
                                            value: String(registro.id),
                                            label: `${nombreCatalogo(registro)}${registro.nivel_id && !filtros.nivel_id ? ` · ${catalogos.niveles.find((nivel) => nivel.id === registro.nivel_id)?.descripcion ?? ''}` : ''}`,
                                        })),
                                ];

                                return (
                                    <EureSelectGroup
                                        key={campo}
                                        name={`filtro_${campo}`}
                                        label={label}
                                        options={opciones}
                                        value={
                                            opciones.find(
                                                (opcion) =>
                                                    opcion.value ===
                                                    filtros[campo],
                                            ) ?? null
                                        }
                                        onChange={(opcion) =>
                                            setFiltros((actuales) => ({
                                                ...actuales,
                                                ...(campo === 'nivel_id'
                                                    ? {
                                                          curso_id: '',
                                                          plan_estudio_id: '',
                                                      }
                                                    : {}),
                                                [campo]: String(
                                                    opcion?.value ?? '',
                                                ),
                                            }))
                                        }
                                    />
                                );
                            })}
                            <EureSelectGroup
                                name="filtro_estado"
                                label="Estado"
                                options={estados}
                                value={
                                    estados.find(
                                        (opcion) =>
                                            opcion.value === filtros.estado,
                                    ) ?? estados[0]
                                }
                                onChange={(opcion) =>
                                    setFiltros({
                                        ...filtros,
                                        estado: String(opcion?.value ?? ''),
                                    })
                                }
                            />
                            <div className="col-span-full flex flex-wrap items-end justify-between gap-3 2xl:flex-col 2xl:items-stretch">
                                <p
                                    className="text-sm text-muted-foreground"
                                    aria-live="polite"
                                >
                                    {resultados.filtrados} de {resultados.total}{' '}
                                    grupos
                                    {pendientes && (
                                        <span className="text-educatio-warning">
                                            {' '}
                                            · Cambios sin aplicar ·{' '}
                                            <button
                                                type="submit"
                                                className="underline underline-offset-4"
                                            >
                                                Actualizar
                                            </button>
                                        </span>
                                    )}
                                </p>
                                <div className="flex items-center gap-3 2xl:justify-end">
                                    <Link
                                        href={index()}
                                        className="text-sm text-primary underline underline-offset-4"
                                    >
                                        Limpiar filtros
                                    </Link>
                                    <EureButtonPrimary
                                        type="submit"
                                        loading={filtrando}
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
                            data={filas}
                            globalFilterPlaceholder="Buscar en resultados..."
                            showExportButtons={false}
                            initialPageSize={10}
                            initialSorting={[{ id: 'id', desc: true }]}
                            getRowClassName={(grupo) =>
                                grupo.activo ? '' : 'opacity-65'
                            }
                            acciones={[
                                {
                                    label: 'Ver',
                                    icon: EyeIcon,
                                    color: 'blue',
                                    onClick: (grupo) =>
                                        router.visit(show(grupo.id, contexto)),
                                },
                                {
                                    label: 'Editar',
                                    icon: PencilIcon,
                                    onClick: (grupo) =>
                                        router.visit(edit(grupo.id, contexto)),
                                },
                                {
                                    label: 'Desactivar',
                                    icon: Trash2Icon,
                                    color: 'red',
                                    hidden: (grupo) =>
                                        !grupo.activo || procesando !== null,
                                    onClick: (grupo) =>
                                        void cambiarEstado(grupo),
                                },
                                {
                                    label: 'Reactivar',
                                    icon: RotateCcwIcon,
                                    color: 'green',
                                    hidden: (grupo) =>
                                        grupo.activo || procesando !== null,
                                    onClick: (grupo) =>
                                        void cambiarEstado(grupo),
                                },
                            ]}
                        />
                    </EducatioCard>
                </div>
            </div>
        </>
    );
}

GruposIndex.layout = () => ({
    breadcrumbs: [{ title: 'Grupos', href: index.url() }],
});
