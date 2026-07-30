import { Head, Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { PencilIcon, PlusIcon, PowerIcon, RotateCcwIcon } from 'lucide-react';
import { useMemo } from 'react';

import CursoController from '@/actions/App/Http/Controllers/Academico/CursoController';
import ActivoBadge from '@/components/activo-badge';
import Heading from '@/components/heading';
import { EureButtonPrimary, EureDataTable } from '@/components/ui/eure';
import type { EureDataTableAccion } from '@/components/ui/eure';
import { formatId } from '@/lib/id';
import { index } from '@/routes/academico/cursos';

type Curso = {
    id: number;
    activo: boolean;
    nivel_id: number;
    codigo: string;
    descripcion: string;
    orden: number;
    nivel: { id: number; codigo: string; descripcion: string } | null;
};

const confirmar = (title: string, text: string): boolean =>
    typeof window === 'undefined'
        ? true
        : window.confirm(`${title}\n\n${text}`);

export default function CursosIndex({ cursos }: { cursos: Curso[] }) {
    const columns = useMemo<ColumnDef<Curso>[]>(
        () => [
            {
                id: 'id',
                header: '#',
                accessorFn: (row) => row.id,
                cell: ({ row }) => formatId(row.original.id),
                meta: { className: 'text-center', exportHeader: 'ID' },
            },
            {
                id: 'nivel',
                header: 'Nivel',
                accessorFn: (row) => row.nivel?.descripcion ?? '',
                meta: { exportHeader: 'Nivel' },
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
                id: 'orden',
                header: 'Orden',
                accessorFn: (row) => row.orden,
                meta: { className: 'text-center', exportHeader: 'Orden' },
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

    const acciones = useMemo<EureDataTableAccion<Curso>[]>(
        () => [
            {
                label: 'Editar',
                icon: PencilIcon,
                onClick: (curso) =>
                    router.get(CursoController.edit(curso.id).url),
            },
            {
                label: 'Dar de baja',
                icon: PowerIcon,
                color: 'red',
                hidden: (curso) => !curso.activo,
                onClick: (curso) => {
                    if (
                        confirmar(
                            'Dar de baja',
                            `¿Dar de baja el curso "${curso.descripcion}"?`,
                        )
                    ) {
                        router.patch(CursoController.desactivar(curso.id).url);
                    }
                },
            },
            {
                label: 'Reactivar',
                icon: RotateCcwIcon,
                color: 'green',
                hidden: (curso) => curso.activo,
                onClick: (curso) => {
                    if (
                        confirmar(
                            'Reactivar',
                            `¿Reactivar el curso "${curso.descripcion}"?`,
                        )
                    ) {
                        router.patch(CursoController.reactivar(curso.id).url);
                    }
                },
            },
        ],
        [],
    );

    return (
        <>
            <Head title="Cursos" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Cursos"
                        description="Grados y años por nivel (1er Grado, 1er Año…)"
                    />
                    <Link href={CursoController.create().url}>
                        <EureButtonPrimary type="button">
                            <PlusIcon className="size-4" />
                            Nuevo curso
                        </EureButtonPrimary>
                    </Link>
                </div>

                <EureDataTable
                    columns={columns}
                    data={cursos}
                    acciones={acciones}
                    globalFilterPlaceholder="Buscar cursos..."
                />
            </div>
        </>
    );
}

CursosIndex.layout = {
    breadcrumbs: [{ title: 'Cursos', href: index() }],
};
