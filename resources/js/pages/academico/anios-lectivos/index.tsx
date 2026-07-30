import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    CheckIcon,
    PencilIcon,
    PlusIcon,
    PowerIcon,
    RotateCcwIcon,
} from 'lucide-react';
import { useMemo } from 'react';

import AnioLectivoController from '@/actions/App/Http/Controllers/Academico/AnioLectivoController';
import ActivoBadge from '@/components/activo-badge';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { EureButtonPrimary, EureDataTable } from '@/components/ui/eure';
import type { EureDataTableAccion } from '@/components/ui/eure';
import { formatId } from '@/lib/id';
import { index } from '@/routes/academico/anios-lectivos';

type AnioLectivo = {
    id: number;
    activo: boolean;
    anio: number;
    vigente: boolean;
};

const confirmar = (title: string, text: string): boolean =>
    typeof window === 'undefined'
        ? true
        : window.confirm(`${title}\n\n${text}`);

export default function AniosLectivosIndex({
    aniosLectivos,
}: {
    aniosLectivos: AnioLectivo[];
}) {
    const columns = useMemo<ColumnDef<AnioLectivo>[]>(
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
                accessorFn: (row) => row.anio,
                meta: { className: 'text-center', exportHeader: 'Año' },
            },
            {
                id: 'vigente',
                header: 'Vigente',
                accessorFn: (row) => (row.vigente ? 'Sí' : 'No'),
                cell: ({ row }) =>
                    row.original.vigente ? (
                        <Badge
                            variant="outline"
                            className="border-primary/40 bg-primary/10 text-primary"
                        >
                            <CheckIcon className="size-3" />
                            Vigente
                        </Badge>
                    ) : (
                        <span className="text-muted-foreground">—</span>
                    ),
                meta: { className: 'text-center', exportHeader: 'Vigente' },
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

    const acciones = useMemo<EureDataTableAccion<AnioLectivo>[]>(
        () => [
            {
                label: 'Editar',
                icon: PencilIcon,
                onClick: (anio) =>
                    router.get(AnioLectivoController.edit(anio.id).url),
            },
            {
                label: 'Dar de baja',
                icon: PowerIcon,
                color: 'red',
                hidden: (anio) => !anio.activo,
                onClick: (anio) => {
                    if (
                        confirmar(
                            'Dar de baja',
                            `¿Dar de baja el año lectivo ${anio.anio}?`,
                        )
                    ) {
                        router.patch(
                            AnioLectivoController.desactivar(anio.id).url,
                        );
                    }
                },
            },
            {
                label: 'Reactivar',
                icon: RotateCcwIcon,
                color: 'green',
                hidden: (anio) => anio.activo,
                onClick: (anio) => {
                    if (
                        confirmar(
                            'Reactivar',
                            `¿Reactivar el año lectivo ${anio.anio}?`,
                        )
                    ) {
                        router.patch(
                            AnioLectivoController.reactivar(anio.id).url,
                        );
                    }
                },
            },
        ],
        [],
    );

    return (
        <>
            <Head title="Años lectivos" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Años lectivos"
                        description="Ciclos lectivos (2025, 2026…)"
                    />
                    <Link href={AnioLectivoController.create().url}>
                        <EureButtonPrimary type="button">
                            <PlusIcon className="size-4" />
                            Nuevo año lectivo
                        </EureButtonPrimary>
                    </Link>
                </div>

                <EureDataTable
                    columns={columns}
                    data={aniosLectivos}
                    acciones={acciones}
                    globalFilterPlaceholder="Buscar años lectivos..."
                />
            </div>
        </>
    );
}

AniosLectivosIndex.layout = {
    breadcrumbs: [{ title: 'Años lectivos', href: index() }],
};
