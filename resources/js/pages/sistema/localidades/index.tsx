import type { UrlMethodPair } from '@inertiajs/core';
import { Head, router, useForm } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { PencilIcon, PlusIcon, RotateCcwIcon, Trash2Icon } from 'lucide-react';
import { useMemo, useState } from 'react';
import LocalidadController from '@/actions/App/Http/Controllers/Sistema/LocalidadController';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';

type Provincia = {
    id: number;
    activo: boolean;
    codigo: string;
    nombre: string;
};

type Localidad = {
    id: number;
    activo: boolean;
    provincia_id: number;
    nombre: string;
    codigo_postal: string | null;
    provincia: Provincia;
};

type FormularioLocalidad = {
    provincia_id: number | '';
    nombre: string;
    codigo_postal: string;
};

type Props = {
    localidades: Localidad[];
    provincias: Provincia[];
};

const FORMULARIO_VACIO: FormularioLocalidad = {
    provincia_id: '',
    nombre: '',
    codigo_postal: '',
};

export default function LocalidadesIndex({ localidades, provincias }: Props) {
    const [dialogoAbierto, setDialogoAbierto] = useState(false);
    const [editando, setEditando] = useState<Localidad | null>(null);
    const [cambiandoEstado, setCambiandoEstado] = useState<number | null>(null);
    const form = useForm<FormularioLocalidad>(FORMULARIO_VACIO);

    const opcionesProvincia = useMemo<EureSelectOption[]>(() => {
        return provincias
            .filter(
                (provincia) =>
                    provincia.activo ||
                    (editando !== null &&
                        provincia.id === editando.provincia_id),
            )
            .map((provincia) => ({
                value: provincia.id,
                label: `${provincia.nombre}${provincia.activo ? '' : ' (inactiva)'}`,
            }));
    }, [editando, provincias]);

    const provinciaSeleccionada =
        opcionesProvincia.find(
            (opcion) => opcion.value === form.data.provincia_id,
        ) ?? null;

    const abrirAlta = () => {
        setEditando(null);
        form.setData(FORMULARIO_VACIO);
        form.clearErrors();
        setDialogoAbierto(true);
    };

    const abrirEdicion = (localidad: Localidad) => {
        setEditando(localidad);
        form.setData({
            provincia_id: localidad.provincia_id,
            nombre: localidad.nombre,
            codigo_postal: localidad.codigo_postal ?? '',
        });
        form.clearErrors();
        setDialogoAbierto(true);
    };

    const completarGuardado = () => {
        setDialogoAbierto(false);
        setEditando(null);
        form.resetAndClearErrors();
    };

    const cerrarDialogo = () => {
        if (!form.processing) {
            completarGuardado();
        }
    };

    const guardar = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const destino: UrlMethodPair = editando
            ? LocalidadController.update(editando.id)
            : LocalidadController.store();

        form.submit(destino, {
            preserveScroll: true,
            onSuccess: completarGuardado,
        });
    };

    const cambiarEstado = async (localidad: Localidad) => {
        const confirmado = await EureConfirmSwal()({
            title: `${localidad.activo ? 'Desactivar' : 'Reactivar'} localidad`,
            text: localidad.activo
                ? `“${localidad.nombre}” dejará de estar disponible para nuevas operaciones.`
                : `“${localidad.nombre}” volverá a estar disponible.`,
            confirmText: localidad.activo ? 'Desactivar' : 'Reactivar',
        });

        if (!confirmado) {
            return;
        }

        setCambiandoEstado(localidad.id);
        router.visit(
            localidad.activo
                ? LocalidadController.desactivar(localidad.id)
                : LocalidadController.reactivar(localidad.id),
            {
                preserveScroll: true,
                onFinish: () => setCambiandoEstado(null),
            },
        );
    };

    const columnas = useMemo<ColumnDef<Localidad>[]>(
        () => [
            {
                id: 'id',
                header: '#',
                accessorFn: (localidad) => localidad.id,
                meta: { className: 'text-center tabular-nums' },
            },
            {
                id: 'nombre',
                header: 'Localidad',
                accessorFn: (localidad) => localidad.nombre,
            },
            {
                id: 'provincia',
                header: 'Provincia / país',
                accessorFn: (localidad) => localidad.provincia.nombre,
                cell: ({ row }) => (
                    <span>
                        {row.original.provincia.nombre}
                        {!row.original.provincia.activo && (
                            <span className="ml-1 text-xs text-muted-foreground">
                                (inactiva)
                            </span>
                        )}
                    </span>
                ),
            },
            {
                id: 'codigo_postal',
                header: 'Código postal',
                accessorFn: (localidad) => localidad.codigo_postal ?? '',
                meta: { className: 'text-center' },
                cell: ({ getValue }) => String(getValue()) || '—',
            },
            {
                id: 'estado',
                header: 'Estado',
                accessorFn: (localidad) =>
                    localidad.activo ? 'Activo' : 'Inactivo',
                meta: { className: 'text-center' },
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
            },
        ],
        [],
    );

    return (
        <>
            <Head title="Localidades" />

            <div className="flex flex-1 flex-col gap-5 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                            Localidades
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Localidades y códigos postales organizados por
                            provincia o país.
                        </p>
                    </div>

                    <EureButtonPrimary onClick={abrirAlta} className="shrink-0">
                        <PlusIcon className="size-4" />
                        Nueva localidad
                    </EureButtonPrimary>
                </div>

                <EureDataTable
                    columns={columnas}
                    data={localidades}
                    globalFilterPlaceholder="Buscar localidades..."
                    showExportButtons={false}
                    initialPageSize={10}
                    getRowClassName={(localidad) =>
                        localidad.activo ? '' : 'opacity-65'
                    }
                    acciones={[
                        {
                            label: 'Editar',
                            icon: PencilIcon,
                            onClick: abrirEdicion,
                        },
                        {
                            label: 'Desactivar',
                            icon: Trash2Icon,
                            color: 'red',
                            hidden: (localidad) =>
                                !localidad.activo ||
                                cambiandoEstado === localidad.id,
                            onClick: (localidad) =>
                                void cambiarEstado(localidad),
                        },
                        {
                            label: 'Reactivar',
                            icon: RotateCcwIcon,
                            color: 'green',
                            hidden: (localidad) =>
                                localidad.activo ||
                                cambiandoEstado === localidad.id,
                            onClick: (localidad) =>
                                void cambiarEstado(localidad),
                        },
                    ]}
                />
            </div>

            <Dialog
                open={dialogoAbierto}
                onOpenChange={(abierto) => {
                    if (!abierto) {
                        cerrarDialogo();
                    }
                }}
            >
                <DialogContent className="sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>
                            {editando ? 'Editar' : 'Nueva'} localidad
                        </DialogTitle>
                        <DialogDescription>
                            Completá los datos y guardá los cambios.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={guardar} className="space-y-4">
                        <EureInputGroup
                            label="Nombre"
                            name="nombre"
                            value={form.data.nombre}
                            onChange={(event) =>
                                form.setData('nombre', event.target.value)
                            }
                            error={Boolean(form.errors.nombre)}
                            errorMessage={form.errors.nombre}
                            maxLength={120}
                            autoFocus
                            required
                        />

                        <EureSelectGroup
                            label="Provincia / país"
                            name="provincia_id"
                            value={provinciaSeleccionada}
                            onChange={(opcion) =>
                                form.setData(
                                    'provincia_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesProvincia}
                            placeholder="Buscar y seleccionar una provincia"
                            error={Boolean(form.errors.provincia_id)}
                            errorMessage={form.errors.provincia_id}
                            required
                        />

                        <EureInputGroup
                            label="Código postal"
                            name="codigo_postal"
                            value={form.data.codigo_postal}
                            onChange={(event) =>
                                form.setData(
                                    'codigo_postal',
                                    event.target.value.toLocaleUpperCase(
                                        'es-AR',
                                    ),
                                )
                            }
                            error={Boolean(form.errors.codigo_postal)}
                            errorMessage={form.errors.codigo_postal}
                            maxLength={10}
                            classNameWrapper="max-w-52"
                        />

                        <DialogFooter className="pt-2">
                            <EureButtonSecondary
                                onClick={cerrarDialogo}
                                disabled={form.processing}
                            >
                                Cancelar
                            </EureButtonSecondary>
                            <EureButtonPrimary
                                type="submit"
                                loading={form.processing}
                            >
                                Guardar
                            </EureButtonPrimary>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

LocalidadesIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Localidades',
            href: LocalidadController.index().url,
        },
    ],
});
