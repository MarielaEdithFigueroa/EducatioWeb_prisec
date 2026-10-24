import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    DoorOpenIcon,
    EyeIcon,
    PencilIcon,
    PlusIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import { useMemo } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import { formatId } from '@/lib/utils';
import { create, edit, index, show } from '@/routes/academico/aulas';
import type { Aula } from './types';
import { useEstadoAula } from './use-estado-aula';

type AulaFila = Aula & { idF: string };

const columnas: ColumnDef<AulaFila>[] = [
    {
        accessorKey: 'id',
        header: '#',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => (
            <Link
                href={show(row.original.id)}
                aria-label={`Ver aula ${row.original.idF}`}
                className="rounded-sm font-bold text-primary underline underline-offset-4 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                {row.original.idF}
            </Link>
        ),
    },
    {
        accessorKey: 'descripcion',
        header: 'Descripción',
        meta: { className: 'text-left' },
    },
    {
        accessorKey: 'descripcion_larga',
        header: 'Detalle / ubicación',
        meta: { className: 'hidden text-left lg:table-cell' },
        cell: ({ row }) => (
            <span
                className="block max-w-sm truncate"
                title={row.original.descripcion_larga ?? undefined}
            >
                {row.original.descripcion_larga || '—'}
            </span>
        ),
    },
    {
        accessorKey: 'capacidad',
        header: 'Capacidad',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => row.original.capacidad ?? '—',
    },
    {
        id: 'tipo',
        header: 'Tipo',
        accessorFn: (aula) => (aula.es_especial ? 'Especial' : 'Común'),
        meta: { className: 'text-center' },
    },
    {
        accessorKey: 'especialidad',
        header: 'Especialidad',
        meta: { className: 'text-left' },
        cell: ({ row }) => row.original.especialidad || '—',
    },
    {
        id: 'estado',
        header: 'Estado',
        accessorFn: (aula) => (aula.activo ? 'Activa' : 'Inactiva'),
        meta: { className: 'text-center' },
        cell: ({ row }) => (
            <Badge variant="outline">
                {row.original.activo ? 'Activa' : 'Inactiva'}
            </Badge>
        ),
    },
];

export default function AulasIndex({ aulas }: { aulas: Aula[] }) {
    const filas = useMemo<AulaFila[]>(
        () => aulas.map((aula) => ({ ...aula, idF: formatId(aula.id, 3) })),
        [aulas],
    );
    const { procesando, cambiarEstado } = useEstadoAula();

    return (
        <>
            <Head title="Aulas" />
            <div className="flex flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                            <DoorOpenIcon className="size-7 text-educatio-icon" />
                            Aulas
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Espacios, capacidad y especialidades.
                        </p>
                    </div>
                    <EureButtonPrimary onClick={() => router.visit(create())}>
                        <PlusIcon className="size-4" />
                        Nueva aula
                    </EureButtonPrimary>
                </EducatioCard>
                <EducatioCard className="min-w-0">
                    <EureDataTable
                        variant="plain"
                        columns={columnas}
                        data={filas}
                        initialSorting={[{ id: 'descripcion', desc: false }]}
                        globalFilterPlaceholder="Buscar aula, ubicación o especialidad..."
                        showExportButtons={false}
                        initialPageSize={10}
                        getRowClassName={(aula) =>
                            aula.activo ? '' : 'opacity-65'
                        }
                        acciones={[
                            {
                                label: 'Ver',
                                icon: EyeIcon,
                                color: 'blue',
                                onClick: (aula) => router.visit(show(aula.id)),
                            },
                            {
                                label: 'Editar',
                                icon: PencilIcon,
                                onClick: (aula) => router.visit(edit(aula.id)),
                            },
                            {
                                label: 'Desactivar',
                                icon: Trash2Icon,
                                color: 'red',
                                hidden: (aula) =>
                                    !aula.activo || procesando !== null,
                                onClick: (aula) => void cambiarEstado(aula),
                            },
                            {
                                label: 'Reactivar',
                                icon: RotateCcwIcon,
                                color: 'green',
                                hidden: (aula) =>
                                    aula.activo || procesando !== null,
                                onClick: (aula) => void cambiarEstado(aula),
                            },
                        ]}
                    />
                </EducatioCard>
            </div>
        </>
    );
}

AulasIndex.layout = () => ({
    breadcrumbs: [{ title: 'Aulas', href: index.url() }],
});
