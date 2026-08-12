import type { UrlMethodPair } from '@inertiajs/core';
import { Head, router, useForm } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { PencilIcon, PlusIcon, RotateCcwIcon, Trash2Icon } from 'lucide-react';
import { useMemo, useState } from 'react';
import CursoController from '@/actions/App/Http/Controllers/Academico/CursoController';
import DivisionController from '@/actions/App/Http/Controllers/Academico/DivisionController';
import NivelController from '@/actions/App/Http/Controllers/Academico/NivelController';
import PlanEstudioController from '@/actions/App/Http/Controllers/Academico/PlanEstudioController';
import TurnoController from '@/actions/App/Http/Controllers/Academico/TurnoController';
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
import EureCard from '@/components/ui/eure/EureCard';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';

type CatalogoClave =
    | 'niveles'
    | 'planes_estudio'
    | 'turnos'
    | 'cursos'
    | 'divisiones';

type Catalogo = {
    clave: CatalogoClave;
    titulo: string;
    singular: string;
    descripcion: string;
    usa_codigo: boolean;
    usa_nivel: boolean;
    usa_orden: boolean;
};

type Nivel = {
    id: number;
    codigo: string;
    descripcion: string;
    activo: boolean;
};

type RegistroCatalogo = {
    id: number;
    activo: boolean;
    codigo?: string;
    descripcion: string;
    nivel_id?: number;
    orden?: number;
    nivel?: Nivel;
};

type FormularioCatalogo = {
    codigo: string;
    descripcion: string;
    nivel_id: number | '';
    orden: number | '';
};

type AccionesCatalogo = {
    index: () => UrlMethodPair;
    store: () => UrlMethodPair;
    update: (id: number) => UrlMethodPair;
    desactivar: (id: number) => UrlMethodPair;
    reactivar: (id: number) => UrlMethodPair;
};

type Props = {
    catalogo: Catalogo;
    registros: RegistroCatalogo[];
    niveles: Nivel[];
};

const ACCIONES: Record<CatalogoClave, AccionesCatalogo> = {
    niveles: {
        index: NivelController.index,
        store: NivelController.store,
        update: (id) => NivelController.update(String(id)),
        desactivar: (id) => NivelController.desactivar(String(id)),
        reactivar: (id) => NivelController.reactivar(String(id)),
    },
    planes_estudio: {
        index: PlanEstudioController.index,
        store: PlanEstudioController.store,
        update: PlanEstudioController.update,
        desactivar: PlanEstudioController.desactivar,
        reactivar: PlanEstudioController.reactivar,
    },
    turnos: {
        index: TurnoController.index,
        store: TurnoController.store,
        update: TurnoController.update,
        desactivar: TurnoController.desactivar,
        reactivar: TurnoController.reactivar,
    },
    cursos: {
        index: CursoController.index,
        store: CursoController.store,
        update: CursoController.update,
        desactivar: CursoController.desactivar,
        reactivar: CursoController.reactivar,
    },
    divisiones: {
        index: DivisionController.index,
        store: DivisionController.store,
        update: DivisionController.update,
        desactivar: DivisionController.desactivar,
        reactivar: DivisionController.reactivar,
    },
};

const FORMULARIO_VACIO: FormularioCatalogo = {
    codigo: '',
    descripcion: '',
    nivel_id: '',
    orden: 0,
};

export default function CatalogosIndex({
    catalogo,
    registros,
    niveles,
}: Props) {
    const [dialogoAbierto, setDialogoAbierto] = useState(false);
    const [editando, setEditando] = useState<RegistroCatalogo | null>(null);
    const [cambiandoEstado, setCambiandoEstado] = useState<number | null>(null);
    const form = useForm<FormularioCatalogo>(FORMULARIO_VACIO);
    const acciones = ACCIONES[catalogo.clave];

    const opcionesNivel = useMemo<EureSelectOption[]>(() => {
        return niveles
            .filter(
                (nivel) =>
                    nivel.activo ||
                    (editando !== null && nivel.id === editando.nivel_id),
            )
            .map((nivel) => ({
                value: nivel.id,
                label: `${nivel.codigo} · ${nivel.descripcion}${nivel.activo ? '' : ' (inactivo)'}`,
            }));
    }, [editando, niveles]);

    const nivelSeleccionado =
        opcionesNivel.find((opcion) => opcion.value === form.data.nivel_id) ??
        null;

    const abrirAlta = () => {
        setEditando(null);
        form.setData(FORMULARIO_VACIO);
        form.clearErrors();
        setDialogoAbierto(true);
    };

    const abrirEdicion = (registro: RegistroCatalogo) => {
        setEditando(registro);
        form.setData({
            codigo: registro.codigo ?? '',
            descripcion: registro.descripcion,
            nivel_id: registro.nivel_id ?? '',
            orden: registro.orden ?? 0,
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

        const destino = editando
            ? acciones.update(editando.id)
            : acciones.store();

        form.submit(destino, {
            preserveScroll: true,
            onSuccess: completarGuardado,
        });
    };

    const cambiarEstado = async (registro: RegistroCatalogo) => {
        const confirmado = await EureConfirmSwal()({
            title: `${registro.activo ? 'Desactivar' : 'Reactivar'} ${catalogo.singular}`,
            text: registro.activo
                ? `“${registro.descripcion}” dejará de estar disponible para nuevas operaciones.`
                : `“${registro.descripcion}” volverá a estar disponible.`,
            confirmText: registro.activo ? 'Desactivar' : 'Reactivar',
        });

        if (!confirmado) {
            return;
        }

        setCambiandoEstado(registro.id);
        router.visit(
            registro.activo
                ? acciones.desactivar(registro.id)
                : acciones.reactivar(registro.id),
            {
                preserveScroll: true,
                onFinish: () => setCambiandoEstado(null),
            },
        );
    };

    const columnas = useMemo<ColumnDef<RegistroCatalogo>[]>(() => {
        const definiciones: ColumnDef<RegistroCatalogo>[] = [
            {
                id: 'id',
                header: '#',
                accessorFn: (registro) => registro.id,
                meta: { className: 'text-center tabular-nums' },
            },
        ];

        if (catalogo.usa_codigo) {
            definiciones.push({
                id: 'codigo',
                header: 'Código',
                accessorFn: (registro) => registro.codigo ?? '',
                cell: ({ getValue }) => (
                    <span className="font-mono text-xs font-semibold tracking-wide">
                        {String(getValue())}
                    </span>
                ),
            });
        }

        definiciones.push({
            id: 'descripcion',
            header: 'Descripción',
            accessorFn: (registro) => registro.descripcion,
        });

        if (catalogo.usa_nivel) {
            definiciones.push({
                id: 'nivel',
                header: 'Nivel',
                accessorFn: (registro) => registro.nivel?.descripcion ?? '',
                cell: ({ row }) => (
                    <span>
                        {row.original.nivel?.descripcion}
                        {!row.original.nivel?.activo && (
                            <span className="ml-1 text-xs text-muted-foreground">
                                (inactivo)
                            </span>
                        )}
                    </span>
                ),
            });
        }

        if (catalogo.usa_orden) {
            definiciones.push({
                id: 'orden',
                header: 'Orden',
                accessorFn: (registro) => registro.orden ?? 0,
                meta: { className: 'text-center tabular-nums' },
            });
        }

        definiciones.push({
            id: 'estado',
            header: 'Estado',
            accessorFn: (registro) => (registro.activo ? 'Activo' : 'Inactivo'),
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
        });

        return definiciones;
    }, [catalogo]);

    return (
        <>
            <Head title={catalogo.titulo} />

            <div className="flex flex-1 flex-col gap-5 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                            {catalogo.titulo}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {catalogo.descripcion}
                        </p>
                    </div>

                    <EureButtonPrimary onClick={abrirAlta} className="shrink-0">
                        <PlusIcon className="size-4" />
                        Nuevo {catalogo.singular}
                    </EureButtonPrimary>
                </div>

                <EureCard className="min-w-0">
                    <EureDataTable
                        columns={columnas}
                        data={registros}
                        globalFilterPlaceholder={`Buscar ${catalogo.titulo.toLocaleLowerCase('es-AR')}...`}
                        showExportButtons={false}
                        initialPageSize={10}
                        getRowClassName={(registro) =>
                            registro.activo ? '' : 'opacity-65'
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
                                hidden: (registro) =>
                                    !registro.activo ||
                                    cambiandoEstado === registro.id,
                                onClick: (registro) =>
                                    void cambiarEstado(registro),
                            },
                            {
                                label: 'Reactivar',
                                icon: RotateCcwIcon,
                                color: 'green',
                                hidden: (registro) =>
                                    registro.activo ||
                                    cambiandoEstado === registro.id,
                                onClick: (registro) =>
                                    void cambiarEstado(registro),
                            },
                        ]}
                    />
                </EureCard>
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
                            {editando ? 'Editar' : 'Nuevo'} {catalogo.singular}
                        </DialogTitle>
                        <DialogDescription>
                            Completá los datos y guardá los cambios.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={guardar} className="space-y-4">
                        {catalogo.usa_codigo && (
                            <EureInputGroup
                                label="Código"
                                name="codigo"
                                value={form.data.codigo}
                                onChange={(event) =>
                                    form.setData(
                                        'codigo',
                                        event.target.value.toLocaleUpperCase(
                                            'es-AR',
                                        ),
                                    )
                                }
                                error={Boolean(form.errors.codigo)}
                                errorMessage={form.errors.codigo}
                                maxLength={20}
                                autoFocus
                                required
                                classNameWrapper="max-w-52"
                            />
                        )}

                        <EureInputGroup
                            label="Descripción"
                            name="descripcion"
                            value={form.data.descripcion}
                            onChange={(event) =>
                                form.setData('descripcion', event.target.value)
                            }
                            error={Boolean(form.errors.descripcion)}
                            errorMessage={form.errors.descripcion}
                            maxLength={
                                catalogo.clave === 'planes_estudio'
                                    ? 150
                                    : catalogo.clave === 'cursos'
                                      ? 100
                                      : 50
                            }
                            autoFocus={!catalogo.usa_codigo}
                            required
                        />

                        {catalogo.usa_nivel && (
                            <EureSelectGroup
                                label="Nivel"
                                name="nivel_id"
                                value={nivelSeleccionado}
                                onChange={(opcion) =>
                                    form.setData(
                                        'nivel_id',
                                        opcion === null
                                            ? ''
                                            : Number(opcion.value),
                                    )
                                }
                                options={opcionesNivel}
                                placeholder="Buscar y seleccionar un nivel"
                                error={Boolean(form.errors.nivel_id)}
                                errorMessage={form.errors.nivel_id}
                                required
                            />
                        )}

                        {catalogo.usa_orden && (
                            <EureInputGroup
                                label="Orden"
                                name="orden"
                                type="number"
                                value={form.data.orden}
                                onChange={(event) =>
                                    form.setData(
                                        'orden',
                                        event.target.value === ''
                                            ? ''
                                            : Number(event.target.value),
                                    )
                                }
                                error={Boolean(form.errors.orden)}
                                errorMessage={form.errors.orden}
                                min={0}
                                max={65535}
                                required
                                classNameWrapper="max-w-32"
                            />
                        )}

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

CatalogosIndex.layout = (props: Props) => ({
    breadcrumbs: [
        {
            title: props.catalogo.titulo,
            href: ACCIONES[props.catalogo.clave].index().url,
        },
    ],
});
