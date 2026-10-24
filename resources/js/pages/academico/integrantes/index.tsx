import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    EyeIcon,
    ListFilterIcon,
    PencilIcon,
    PlusIcon,
    PrinterIcon,
    RotateCcwIcon,
    Trash2Icon,
    UserCogIcon,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import { formatCuilt } from '@/lib/cuit';
import { formatDate } from '@/lib/date';
import {
    create,
    edit,
    imprimir,
    index,
    show,
} from '@/routes/academico/integrantes';
import { contextoIntegrante } from './types';
import type { IntegranteFila, FiltrosIntegrante, FuncionEquipo } from './types';
import { useEstadoIntegrante } from './use-estado-integrante';

type Props = {
    integrantes: IntegranteFila[];
    filters: FiltrosIntegrante;
    funcionesEquipo: FuncionEquipo[];
    resultados: { total: number; filtrados: number };
};
export default function IntegrantesIndex({
    integrantes,
    filters,
    funcionesEquipo,
    resultados,
}: Props) {
    const [filtros, setFiltros] = useState(filters);
    const [filtrando, setFiltrando] = useState(false);
    const estado = useEstadoIntegrante();
    const contexto = contextoIntegrante(filters);
    const pendientes = Object.keys(filters).some(
        (clave) =>
            filters[clave as keyof FiltrosIntegrante] !==
            filtros[clave as keyof FiltrosIntegrante],
    );
    const columnas = useMemo<ColumnDef<IntegranteFila>[]>(
        () => [
            {
                id: 'nombre',
                header: 'Apellido y nombre',
                accessorFn: (integrante) =>
                    `${integrante.apellido}, ${integrante.nombre}`,
                cell: ({ row, getValue }) => (
                    <Link
                        href={show(
                            row.original.id,
                            contextoIntegrante(filters),
                        )}
                        className="font-semibold text-primary underline underline-offset-4"
                    >
                        {String(getValue())}
                    </Link>
                ),
            },
            {
                id: 'legajo',
                header: 'Legajo',
                accessorFn: (integrante) => integrante.legajo,
                meta: { className: 'text-center tabular-nums' },
            },
            {
                id: 'documento',
                header: 'Documento',
                accessorFn: (integrante) =>
                    [
                        integrante.tipo_documento?.nombre,
                        integrante.numero_documento,
                    ]
                        .filter(Boolean)
                        .join(' '),
                meta: { className: 'text-center' },
            },
            {
                id: 'cuilt',
                header: 'CUIL/CUIT',
                accessorFn: (integrante) => String(integrante.cuilt ?? ''),
                cell: ({ row }) =>
                    formatCuilt(String(row.original.cuilt ?? '')),
                meta: { className: 'hidden text-center xl:table-cell' },
            },
            {
                id: 'funciones',
                header: 'Funciones',
                accessorFn: (integrante) =>
                    integrante.funciones
                        .map((funcion) => funcion.descripcion)
                        .join(', '),
                cell: ({ row }) => (
                    <div className="flex max-w-64 flex-wrap gap-1">
                        {row.original.funciones.length
                            ? row.original.funciones.map((funcion) => (
                                  <Badge key={funcion.id} variant="outline">
                                      {funcion.descripcion}
                                  </Badge>
                              ))
                            : '—'}
                    </div>
                ),
            },
            {
                id: 'estado',
                header: 'Estado',
                accessorFn: (integrante) =>
                    integrante.activo ? 'Activo' : 'Dado de baja',
                cell: ({ row, getValue }) => (
                    <Badge
                        variant="outline"
                        className={
                            row.original.activo
                                ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                : 'text-muted-foreground'
                        }
                    >
                        {String(getValue())}
                    </Badge>
                ),
            },
            {
                id: 'ingreso',
                header: 'Ingreso',
                accessorFn: (integrante) => integrante.fecha_ingreso ?? '',
                cell: ({ row }) =>
                    formatDate(String(row.original.fecha_ingreso ?? ''), '—'),
                meta: {
                    className: 'hidden text-center tabular-nums xl:table-cell',
                },
            },
        ],
        [filters],
    );
    const buscar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get(index.url(), filtros, {
            only: ['integrantes', 'filters', 'resultados'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setFiltrando(true),
            onSuccess: (page) =>
                setFiltros(page.props.filters as FiltrosIntegrante),
            onFinish: () => setFiltrando(false),
        });
    };
    const filtrosSelect = [
        {
            campo: 'estado' as const,
            label: 'Estado',
            opciones: [
                { value: '', label: 'Todos' },
                { value: 'activos', label: 'Activos' },
                { value: 'inactivos', label: 'Dados de baja' },
            ],
        },
        {
            campo: 'funcion_id' as const,
            label: 'Función',
            opciones: [
                { value: '', label: 'Todas' },
                ...funcionesEquipo.map((funcion) => ({
                    value: String(funcion.id),
                    label: funcion.descripcion,
                })),
            ],
        },
    ];

    return (
        <>
            <Head title="Equipo institucional" />
            <div className="flex flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-semibold">
                            <UserCogIcon className="size-7 text-educatio-icon" />
                            Equipo institucional
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Legajos y funciones del equipo
                        </p>
                    </div>
                    <EureButtonPrimary
                        onClick={() => router.visit(create(contexto))}
                    >
                        <PlusIcon />
                        Nuevo integrante
                    </EureButtonPrimary>
                </EducatioCard>
                <div className="grid gap-4 2xl:grid-cols-[17rem_minmax(0,1fr)] 2xl:items-start">
                    <EducatioCard className="2xl:sticky 2xl:top-24">
                        <form
                            onSubmit={buscar}
                            className="grid gap-3 md:grid-cols-4 2xl:grid-cols-1"
                        >
                            <EureInputGroup
                                name="buscar"
                                label="Buscar (que contenga)"
                                value={filtros.buscar}
                                onChange={(event) =>
                                    setFiltros({
                                        ...filtros,
                                        buscar: event.target.value,
                                    })
                                }
                                placeholder="Apellido, nombre, legajo, documento o CUIL/CUIT"
                            />
                            {filtrosSelect.map(({ campo, label, opciones }) => (
                                <EureSelectGroup
                                    key={campo}
                                    name={campo}
                                    label={label}
                                    options={opciones}
                                    value={
                                        opciones.find(
                                            (opcion) =>
                                                opcion.value === filtros[campo],
                                        ) ?? opciones[0]
                                    }
                                    onChange={(opcion) =>
                                        setFiltros({
                                            ...filtros,
                                            [campo]: String(
                                                opcion?.value ?? '',
                                            ),
                                        })
                                    }
                                />
                            ))}
                            <div className="flex flex-wrap items-center justify-between gap-3 md:col-span-4 2xl:col-span-1">
                                <p className="text-sm text-muted-foreground">
                                    {resultados.filtrados} de {resultados.total}{' '}
                                    resultados
                                    {pendientes && (
                                        <span className="text-educatio-warning">
                                            {' '}
                                            · Cambios sin aplicar
                                        </span>
                                    )}
                                </p>
                                <Link
                                    href={index()}
                                    className="text-sm text-primary underline"
                                >
                                    Limpiar filtros
                                </Link>
                                <EureButtonPrimary
                                    type="submit"
                                    loading={filtrando}
                                >
                                    <ListFilterIcon />
                                    Filtrar
                                </EureButtonPrimary>
                            </div>
                        </form>
                    </EducatioCard>
                    <EducatioCard className="min-w-0">
                        <EureDataTable
                            variant="plain"
                            columns={columnas}
                            data={integrantes}
                            globalFilterPlaceholder="Buscar en resultados..."
                            showExportButtons={false}
                            initialPageSize={10}
                            initialSorting={[{ id: 'nombre', desc: false }]}
                            getRowClassName={(integrante) =>
                                integrante.activo ? '' : 'opacity-65'
                            }
                            acciones={[
                                {
                                    label: 'Ver ficha',
                                    icon: EyeIcon,
                                    color: 'blue',
                                    onClick: (integrante) =>
                                        router.visit(
                                            show(integrante.id, contexto),
                                        ),
                                },
                                {
                                    label: 'Editar',
                                    icon: PencilIcon,
                                    hidden: (integrante) => !integrante.activo,
                                    onClick: (integrante) =>
                                        router.visit(
                                            edit(integrante.id, contexto),
                                        ),
                                },
                                {
                                    label: 'Imprimir ficha',
                                    icon: PrinterIcon,
                                    onClick: (integrante) =>
                                        router.visit(
                                            imprimir(integrante.id, contexto),
                                        ),
                                },
                                {
                                    label: 'Dar de baja',
                                    icon: Trash2Icon,
                                    color: 'red',
                                    hidden: (integrante) =>
                                        !integrante.activo || estado.procesando,
                                    onClick: estado.abrirBaja,
                                },
                                {
                                    label: 'Reincorporar',
                                    icon: RotateCcwIcon,
                                    color: 'green',
                                    hidden: (integrante) =>
                                        integrante.activo || estado.procesando,
                                    onClick: (integrante) =>
                                        void estado.reincorporar(integrante),
                                },
                            ]}
                        />
                    </EducatioCard>
                </div>
            </div>
            {estado.dialogo}
        </>
    );
}
IntegrantesIndex.layout = () => ({
    breadcrumbs: [{ title: 'Equipo institucional', href: index.url() }],
});
