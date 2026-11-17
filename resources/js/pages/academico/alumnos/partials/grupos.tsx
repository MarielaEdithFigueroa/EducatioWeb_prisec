import { Link, router, useForm } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { HistoryIcon, LayersIcon, RefreshCwIcon, SaveIcon } from 'lucide-react';
import type { FormEvent } from 'react';
import { update } from '@/actions/App/Http/Controllers/Academico/AlumnoGrupoController';
import EducatioCard from '@/components/educatio-card';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import { formatDateTime } from '@/lib/date';
import { formatId } from '@/lib/utils';
import { show as verGrupo } from '@/routes/academico/grupos';
import type { AlumnoDetalle, AsignacionGrupo, GrupoDisponible } from './types';

type Fila = AsignacionGrupo & { idF: string };

const columnas: ColumnDef<Fila>[] = [
    {
        accessorKey: 'id',
        header: '#',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => row.original.idF,
    },
    {
        accessorKey: 'grupo_descripcion',
        header: 'Grupo',
        meta: { className: 'text-left' },
        cell: ({ row }) => (
            <Link
                href={verGrupo(row.original.grupo_id)}
                className="rounded-sm text-primary underline underline-offset-4 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                {row.original.grupo_descripcion}
                {!row.original.grupo_activo && ' (inactivo)'}
            </Link>
        ),
    },
    {
        accessorKey: 'fecha_desde',
        header: 'Desde',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => formatDateTime(row.original.fecha_desde),
    },
    {
        accessorKey: 'fecha_hasta',
        header: 'Hasta',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => formatDateTime(row.original.fecha_hasta, 'Vigente'),
    },
    {
        accessorKey: 'motivo_ingreso',
        header: 'Motivo de ingreso',
        meta: { className: 'text-left' },
    },
    {
        accessorKey: 'motivo_egreso',
        header: 'Motivo de egreso',
        meta: { className: 'text-left' },
        cell: ({ row }) => row.original.motivo_egreso ?? '—',
    },
    {
        id: 'usuario_ingreso',
        header: 'Registró ingreso',
        accessorFn: (fila) =>
            fila.auditoria.find((log) => log.accion === 'create')?.login ?? '—',
        meta: { className: 'text-center' },
    },
    {
        id: 'usuario_egreso',
        header: 'Registró egreso',
        accessorFn: (fila) =>
            fila.auditoria.find((log) => log.accion === 'update')?.login ?? '—',
        meta: { className: 'text-center' },
    },
];

export default function GruposAlumno({
    alumno,
    historial,
    grupos,
    version,
}: {
    alumno: AlumnoDetalle;
    historial: AsignacionGrupo[];
    grupos: GrupoDisponible[];
    version: string;
}) {
    const form = useForm({
        grupo_id: alumno.grupo_id,
        version_historial: version,
        motivo: '',
    });
    const confirmar = EureConfirmSwal();
    const opciones = [
        { value: '', label: 'Sin grupo — cerrar asignación' },
        ...grupos.map((grupo) => ({
            value: String(grupo.id),
            label: grupo.descripcion,
        })),
    ];
    const guardar = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (form.processing || form.data.grupo_id === alumno.grupo_id) {
            return;
        }

        if (
            alumno.grupo_id !== null &&
            !(await confirmar({
                title:
                    form.data.grupo_id === null
                        ? 'Cerrar asignación'
                        : 'Cambiar de grupo',
                text: 'La permanencia actual se cerrará y quedará conservada en el historial.',
                confirmText: 'Guardar',
            }))
        ) {
            return;
        }

        form.submit(update(alumno.id), { preserveScroll: true });
    };

    return (
        <div className="space-y-4">
            <EducatioCard>
                <div className="mb-3 flex items-center gap-2">
                    <LayersIcon
                        aria-hidden="true"
                        className="size-5 text-educatio-icon"
                    />
                    <h2 className="text-lg font-semibold">Grupo actual</h2>
                </div>
                <p className="mb-3 text-sm">
                    {alumno.grupo_descripcion ?? 'Sin grupo asignado'}
                </p>
                {alumno.activo ? (
                    <form
                        onSubmit={guardar}
                        className="grid gap-3 md:grid-cols-2"
                    >
                        <EureSelectGroup
                            label="Asignar a"
                            name="grupo_id"
                            autoFocus
                            value={
                                opciones.find(
                                    (opcion) =>
                                        opcion.value ===
                                        String(form.data.grupo_id ?? ''),
                                ) ?? null
                            }
                            onChange={(opcion) =>
                                form.setData(
                                    'grupo_id',
                                    opcion?.value ? Number(opcion.value) : null,
                                )
                            }
                            options={opciones}
                            placeholder="Buscar grupo por ciclo, curso o división"
                            isDisabled={form.processing}
                            error={Boolean(form.errors.grupo_id)}
                            errorMessage={form.errors.grupo_id}
                        />
                        <EureInputGroup
                            label="Motivo del movimiento"
                            name="motivo"
                            required
                            maxLength={500}
                            value={form.data.motivo}
                            onChange={(event) =>
                                form.setData('motivo', event.target.value)
                            }
                            disabled={form.processing}
                            error={Boolean(form.errors.motivo)}
                            errorMessage={form.errors.motivo}
                        />
                        <div className="flex flex-wrap items-center justify-between gap-3 md:col-span-2">
                            <p className="text-sm text-muted-foreground">
                                El movimiento tendrá efecto al guardar.
                            </p>
                            <div className="flex gap-2">
                                {form.hasErrors && (
                                    <EureButtonSecondary
                                        type="button"
                                        disabled={form.processing}
                                        onClick={() => router.reload()}
                                    >
                                        <RefreshCwIcon
                                            aria-hidden="true"
                                            className="size-4"
                                        />{' '}
                                        Recargar ficha
                                    </EureButtonSecondary>
                                )}
                                <EureButtonPrimary
                                    type="submit"
                                    disabled={
                                        form.processing ||
                                        form.data.grupo_id === alumno.grupo_id
                                    }
                                >
                                    <SaveIcon
                                        aria-hidden="true"
                                        className="size-4"
                                    />{' '}
                                    Guardar asignación
                                </EureButtonPrimary>
                            </div>
                        </div>
                        {form.errors.version_historial && (
                            <p
                                role="alert"
                                className="text-sm text-eure-error md:col-span-2"
                            >
                                {form.errors.version_historial}
                            </p>
                        )}
                    </form>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        Reactivá al alumno para asignarle un grupo.
                    </p>
                )}
            </EducatioCard>
            <EducatioCard>
                <div className="mb-3 flex items-center gap-2">
                    <HistoryIcon
                        aria-hidden="true"
                        className="size-5 text-educatio-icon"
                    />
                    <h2 className="text-lg font-semibold">
                        Historial de grupos
                    </h2>
                </div>
                <EureDataTable
                    variant="plain"
                    columns={columnas}
                    data={historial.map((fila) => ({
                        ...fila,
                        idF: formatId(fila.id, 5),
                    }))}
                    initialSorting={[{ id: 'id', desc: true }]}
                    globalFilterPlaceholder="Buscar en el historial..."
                />
            </EducatioCard>
        </div>
    );
}
