import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { PencilIcon, PlusIcon, PowerIcon, RotateCcwIcon } from 'lucide-react';
import { useMemo } from 'react';

import GrupoController from '@/actions/App/Http/Controllers/Academico/GrupoController';
import ActivoBadge from '@/components/activo-badge';
import Heading from '@/components/heading';
import { EureButtonPrimary, EureDataTable } from '@/components/ui/eure';
import type { EureDataTableAccion } from '@/components/ui/eure';
import { formatId } from '@/lib/id';
import { index } from '@/routes/academico/grupos';

type Grupo = {
    id: number;
    activo: boolean;
    anio_lectivo: { id: number; anio: number } | null;
    curso: {
        id: number;
        codigo: string;
        descripcion: string;
        nivel: { id: number; codigo: string; descripcion: string } | null;
    } | null;
    division: { id: number; codigo: string; descripcion: string } | null;
    turno: { id: number; codigo: string; descripcion: string } | null;
};

const confirmar = (title: string, text: string): boolean =>
    typeof window === 'undefined'
        ? true
        : window.confirm(`${title}\n\n${text}`);

const describir = (grupo: Grupo): string =>
    [
        grupo.curso?.descripcion,
        grupo.division?.codigo,
        grupo.turno?.descripcion,
        grupo.anio_lectivo?.anio,
    ]
        .filter(Boolean)
        .join(' · ');

export default function GruposIndex({ grupos }: { grupos: Grupo[] }) {
    const columns = useMemo<ColumnDef<Grupo>[]>(
        () => [
            {
                id: 'id',
                header: '#',
                accessorFn: (row) => row.id,
                cell: ({ row }) => formatId(row.original.id),
                meta: { className: 'text-center', exportHeader: 'ID' },
            },
            {
                id: 'anio',
                header: 'Año',
                accessorFn: (row) => row.anio_lectivo?.anio ?? 0,
                meta: { className: 'text-center', exportHeader: 'Año' },
            },
            {
                id: 'nivel',
                header: 'Nivel',
                accessorFn: (row) => row.curso?.nivel?.descripcion ?? '',
                meta: { exportHeader: 'Nivel' },
            },
            {
                id: 'curso',
                header: 'Curso',
                accessorFn: (row) => row.curso?.descripcion ?? '',
                meta: { exportHeader: 'Curso' },
            },
            {
                id: 'division',
                header: 'División',
                accessorFn: (row) => row.division?.codigo ?? '',
                meta: { className: 'text-center', exportHeader: 'División' },
            },
            {
                id: 'turno',
                header: 'Turno',
                accessorFn: (row) => row.turno?.descripcion ?? '',
                meta: { exportHeader: 'Turno' },
            },
            {
                id: 'activo',
                header: 'Estado',
                accessorFn: (row) => (row.activo ? 'Activo' : 'Inactivo'),
                cell: ({ row }) => <ActivoBadge activo={row.original.activo} />,
                meta: { className: 'text-center', exportHeader: 'Estado' },
            },
        ],
        [],
    );

    const acciones = useMemo<EureDataTableAccion<Grupo>[]>(
        () => [
            {
                label: 'Editar',
                icon: PencilIcon,
                onClick: (grupo) =>
                    router.get(GrupoController.edit(grupo.id).url),
            },
            {
                label: 'Dar de baja',
                icon: PowerIcon,
                color: 'red',
                hidden: (grupo) => !grupo.activo,
                onClick: (grupo) => {
                    if (
                        confirmar(
                            'Dar de baja',
                            `¿Dar de baja el grupo ${describir(grupo)}?`,
                        )
                    ) {
                        router.patch(GrupoController.desactivar(grupo.id).url);
                    }
                },
            },
            {
                label: 'Reactivar',
                icon: RotateCcwIcon,
                color: 'green',
                hidden: (grupo) => grupo.activo,
                onClick: (grupo) => {
                    if (
                        confirmar(
                            'Reactivar',
                            `¿Reactivar el grupo ${describir(grupo)}?`,
                        )
                    ) {
                        router.patch(GrupoController.reactivar(grupo.id).url);
                    }
                },
            },
        ],
        [],
    );

    return (
        <>
            <Head title="Grupos" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Grupos"
                        description="Matrícula por año lectivo: curso + división + turno"
                    />
                    <Link href={GrupoController.create().url}>
                        <EureButtonPrimary type="button">
                            <PlusIcon className="size-4" />
                            Nuevo grupo
                        </EureButtonPrimary>
                    </Link>
                </div>

                <EureDataTable
                    columns={columns}
                    data={grupos}
                    acciones={acciones}
                    globalFilterPlaceholder="Buscar grupos..."
                />
            </div>
        </>
    );
}

GruposIndex.layout = {
    breadcrumbs: [{ title: 'Grupos', href: index() }],
};
