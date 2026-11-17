import { Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import { formatId } from '@/lib/utils';
import { show as verAlumno } from '@/routes/academico/alumnos';

export type AlumnoAsignado = {
    id: number;
    legajo: string;
    apellido: string;
    nombre: string;
};
type Fila = AlumnoAsignado & { idF: string };

const columnas: ColumnDef<Fila>[] = [
    {
        accessorKey: 'id',
        header: '#',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => (
            <Link
                href={verAlumno(row.original.id, {
                    query: { seccion: 'grupos' },
                })}
                className="rounded-sm font-bold text-primary underline underline-offset-4 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                {row.original.idF}
            </Link>
        ),
    },
    {
        accessorKey: 'legajo',
        header: 'Legajo',
        meta: { className: 'text-center tabular-nums' },
    },
    {
        accessorKey: 'apellido',
        header: 'Apellido',
        meta: { className: 'text-left' },
    },
    {
        accessorKey: 'nombre',
        header: 'Nombre',
        meta: { className: 'text-left' },
    },
];

export default function AlumnosAsignados({
    alumnos,
}: {
    alumnos: AlumnoAsignado[];
}) {
    return (
        <EureDataTable
            variant="plain"
            columns={columnas}
            data={alumnos.map((alumno) => ({
                ...alumno,
                idF: formatId(alumno.id, 5),
            }))}
            initialSorting={[{ id: 'id', desc: true }]}
            globalFilterPlaceholder="Buscar en alumnos asignados..."
        />
    );
}
