import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { PencilIcon, PlusIcon, PowerIcon, RotateCcwIcon } from 'lucide-react';
import { useMemo } from 'react';

import ActivoBadge from '@/components/activo-badge';
import Heading from '@/components/heading';
import { EureButtonPrimary, EureDataTable } from '@/components/ui/eure';
import type { EureDataTableAccion } from '@/components/ui/eure';
import { formatId } from '@/lib/id';

import type { CatalogoSimple, CatalogoSimpleController } from './types';

const confirmar = (title: string, text: string): boolean =>
    typeof window === 'undefined'
        ? true
        : window.confirm(`${title}\n\n${text}`);

interface Props {
    titulo: string;
    descripcion: string;
    entidad: string;
    nuevoLabel: string;
    data: CatalogoSimple[];
    controller: CatalogoSimpleController;
    globalFilterPlaceholder?: string;
}

export default function CatalogoSimpleIndex({
    titulo,
    descripcion,
    entidad,
    nuevoLabel,
    data,
    controller,
    globalFilterPlaceholder,
}: Props) {
    const columns = useMemo<ColumnDef<CatalogoSimple>[]>(
        () => [
            {
                id: 'id',
                header: '#',
                accessorFn: (row) => row.id,
                cell: ({ row }) => formatId(row.original.id),
                meta: { className: 'text-center', exportHeader: 'ID' },
            },
            {
                id: 'codigo',
                header: 'Código',
                accessorFn: (row) => row.codigo,
                meta: { exportHeader: 'Código' },
            },
            {
                id: 'descripcion',
                header: 'Descripción',
                accessorFn: (row) => row.descripcion,
                meta: { exportHeader: 'Descripción' },
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

    const acciones = useMemo<EureDataTableAccion<CatalogoSimple>[]>(
        () => [
            {
                label: 'Editar',
                icon: PencilIcon,
                onClick: (item) => router.get(controller.edit(item.id).url),
            },
            {
                label: 'Dar de baja',
                icon: PowerIcon,
                color: 'red',
                hidden: (item) => !item.activo,
                onClick: (item) => {
                    if (
                        confirmar(
                            'Dar de baja',
                            `¿Dar de baja "${item.descripcion}"?`,
                        )
                    ) {
                        router.patch(controller.desactivar(item.id).url);
                    }
                },
            },
            {
                label: 'Reactivar',
                icon: RotateCcwIcon,
                color: 'green',
                hidden: (item) => item.activo,
                onClick: (item) => {
                    if (
                        confirmar(
                            'Reactivar',
                            `¿Reactivar "${item.descripcion}"?`,
                        )
                    ) {
                        router.patch(controller.reactivar(item.id).url);
                    }
                },
            },
        ],
        [controller],
    );

    return (
        <>
            <Head title={titulo} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <Heading title={titulo} description={descripcion} />
                    <Link href={controller.create().url}>
                        <EureButtonPrimary type="button">
                            <PlusIcon className="size-4" />
                            {nuevoLabel}
                        </EureButtonPrimary>
                    </Link>
                </div>

                <EureDataTable
                    columns={columns}
                    data={data}
                    acciones={acciones}
                    globalFilterPlaceholder={
                        globalFilterPlaceholder ?? `Buscar ${entidad}...`
                    }
                />
            </div>
        </>
    );
}
