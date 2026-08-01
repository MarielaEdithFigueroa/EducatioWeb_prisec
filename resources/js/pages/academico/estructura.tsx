import { Form, Head, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    CheckCircle2Icon,
    Edit3Icon,
    PlusIcon,
    PowerIcon,
    RotateCcwIcon,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import {
    desactivar as desactivarAnio,
    marcarVigente,
    reactivar as reactivarAnio,
    store as crearAnio,
    update as actualizarAnio,
} from '@/actions/App/Http/Controllers/Academico/AnioLectivoController';
import {
    desactivar as desactivarCurso,
    reactivar as reactivarCurso,
    store as crearCurso,
    update as actualizarCurso,
} from '@/actions/App/Http/Controllers/Academico/CursoController';
import {
    desactivar as desactivarDivision,
    reactivar as reactivarDivision,
    store as crearDivision,
    update as actualizarDivision,
} from '@/actions/App/Http/Controllers/Academico/DivisionController';
import {
    desactivar as desactivarPlan,
    reactivar as reactivarPlan,
    store as crearPlan,
    update as actualizarPlan,
} from '@/actions/App/Http/Controllers/Academico/PlanEstudioController';
import {
    desactivar as desactivarTurno,
    reactivar as reactivarTurno,
    store as crearTurno,
    update as actualizarTurno,
} from '@/actions/App/Http/Controllers/Academico/TurnoController';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    EureAlert,
    EureButtonPrimary,
    EureButtonSecondary,
    EureCard,
    EureConfirmSwal,
    EureDataTable,
    EureInputGroup,
    EureSelect,
    EureSelectGroup,
} from '@/components/ui/eure';
import type {
    EureDataTableAccion,
    EureSelectOption,
} from '@/components/ui/eure';
import { index as estructuraIndex } from '@/routes/academico/estructura';
import type { AnioLectivo, CatalogoConNivel, Nivel } from '@/types';

type TipoCatalogo = 'curso' | 'division' | 'turno' | 'plan';

type Props = {
    niveles: Nivel[];
    aniosLectivos: AnioLectivo[];
    cursos: CatalogoConNivel[];
    divisiones: CatalogoConNivel[];
    turnos: CatalogoConNivel[];
    planesEstudio: CatalogoConNivel[];
};

type DefinicionFormulario = {
    action: string;
    method: 'post';
};

type ConfiguracionCatalogo = {
    singular: string;
    plural: string;
    usaCodigo: boolean;
    crear: () => DefinicionFormulario;
    actualizar: (id: number) => DefinicionFormulario;
    desactivar: (id: number) => string;
    reactivar: (id: number) => string;
};

const CONFIGURACION_CATALOGOS: Record<TipoCatalogo, ConfiguracionCatalogo> = {
    curso: {
        singular: 'curso',
        plural: 'Cursos',
        usaCodigo: false,
        crear: () => crearCurso.form(),
        actualizar: (id) => actualizarCurso.form(id),
        desactivar: (id) => desactivarCurso.url(id),
        reactivar: (id) => reactivarCurso.url(id),
    },
    division: {
        singular: 'división',
        plural: 'Divisiones',
        usaCodigo: false,
        crear: () => crearDivision.form(),
        actualizar: (id) => actualizarDivision.form(id),
        desactivar: (id) => desactivarDivision.url(id),
        reactivar: (id) => reactivarDivision.url(id),
    },
    turno: {
        singular: 'turno',
        plural: 'Turnos',
        usaCodigo: false,
        crear: () => crearTurno.form(),
        actualizar: (id) => actualizarTurno.form(id),
        desactivar: (id) => desactivarTurno.url(id),
        reactivar: (id) => reactivarTurno.url(id),
    },
    plan: {
        singular: 'plan de estudio',
        plural: 'Planes de estudio',
        usaCodigo: true,
        crear: () => crearPlan.form(),
        actualizar: (id) => actualizarPlan.form(id),
        desactivar: (id) => desactivarPlan.url(id),
        reactivar: (id) => reactivarPlan.url(id),
    },
};

const ETIQUETAS_ESTADO = {
    preparacion: 'En preparación',
    vigente: 'Vigente',
    cerrado: 'Cerrado',
} as const;

function EstadoActivo({ activo }: { activo: boolean }) {
    return (
        <Badge variant={activo ? 'default' : 'secondary'}>
            {activo ? 'Activo' : 'Inactivo'}
        </Badge>
    );
}

function DialogoCatalogo({
    abierto,
    onOpenChange,
    tipo,
    registro,
    niveles,
}: {
    abierto: boolean;
    onOpenChange: (abierto: boolean) => void;
    tipo: TipoCatalogo;
    registro: CatalogoConNivel | null;
    niveles: Nivel[];
}) {
    const configuracion = CONFIGURACION_CATALOGOS[tipo];
    const [nivelId, setNivelId] = useState<number | null>(
        registro?.nivel_id ?? null,
    );

    const opcionesNivel: EureSelectOption[] = niveles
        .filter((nivel) => nivel.activo !== false)
        .map((nivel) => ({ value: nivel.id, label: nivel.descripcion }));
    const nivelSeleccionado =
        opcionesNivel.find((opcion) => opcion.value === nivelId) ?? null;
    const formulario = registro
        ? configuracion.actualizar(registro.id)
        : configuracion.crear();

    return (
        <Dialog open={abierto} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {registro ? 'Editar' : 'Nuevo'} {configuracion.singular}
                    </DialogTitle>
                    <DialogDescription>
                        Los datos se aplican únicamente al nivel seleccionado.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    key={`${tipo}-${registro?.id ?? 'nuevo'}-${abierto}`}
                    {...formulario}
                    options={{ preserveScroll: true }}
                    onSuccess={() => onOpenChange(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <input
                                type="hidden"
                                name="nivel_id"
                                value={nivelId ?? ''}
                            />
                            <EureSelectGroup
                                label="Nivel"
                                name="nivel_id_selector"
                                value={nivelSeleccionado}
                                onChange={(opcion) =>
                                    setNivelId(Number(opcion?.value) || null)
                                }
                                options={opcionesNivel}
                                error={Boolean(errors.nivel_id)}
                                errorMessage={errors.nivel_id}
                                required
                            />

                            {configuracion.usaCodigo && (
                                <EureInputGroup
                                    label="Código"
                                    name="codigo"
                                    defaultValue={registro?.codigo ?? ''}
                                    maxLength={32}
                                    error={Boolean(errors.codigo)}
                                    errorMessage={errors.codigo}
                                    className="max-w-xs"
                                    required
                                />
                            )}

                            <EureInputGroup
                                label="Descripción"
                                name="descripcion"
                                defaultValue={registro?.descripcion ?? ''}
                                maxLength={configuracion.usaCodigo ? 128 : 64}
                                error={Boolean(errors.descripcion)}
                                errorMessage={errors.descripcion}
                                required
                            />

                            {!configuracion.usaCodigo && (
                                <EureInputGroup
                                    label="Orden"
                                    name="orden"
                                    type="number"
                                    min={1}
                                    max={255}
                                    defaultValue={registro?.orden ?? 1}
                                    error={Boolean(errors.orden)}
                                    errorMessage={errors.orden}
                                    className="max-w-32"
                                    required
                                />
                            )}

                            {errors.activo && (
                                <EureAlert message={errors.activo} />
                            )}

                            <DialogFooter>
                                <EureButtonSecondary
                                    onClick={() => onOpenChange(false)}
                                >
                                    Cancelar
                                </EureButtonSecondary>
                                <EureButtonPrimary
                                    type="submit"
                                    loading={processing}
                                >
                                    Guardar
                                </EureButtonPrimary>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function DialogoAnioLectivo({
    abierto,
    onOpenChange,
    registro,
}: {
    abierto: boolean;
    onOpenChange: (abierto: boolean) => void;
    registro: AnioLectivo | null;
}) {
    const formulario = registro
        ? actualizarAnio.form(registro.id)
        : crearAnio.form();

    return (
        <Dialog open={abierto} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {registro ? 'Editar' : 'Nuevo'} año lectivo
                    </DialogTitle>
                    <DialogDescription>
                        Un año nuevo comienza en preparación. Luego puede
                        marcarse como vigente.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    key={`${registro?.id ?? 'nuevo'}-${abierto}`}
                    {...formulario}
                    options={{ preserveScroll: true }}
                    onSuccess={() => onOpenChange(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <EureInputGroup
                                label="Año"
                                name="anio"
                                type="number"
                                min={2000}
                                max={2200}
                                defaultValue={
                                    registro?.anio ??
                                    new Date().getFullYear() + 1
                                }
                                error={Boolean(errors.anio)}
                                errorMessage={errors.anio}
                                className="max-w-40"
                                required
                            />
                            <DialogFooter>
                                <EureButtonSecondary
                                    onClick={() => onOpenChange(false)}
                                >
                                    Cancelar
                                </EureButtonSecondary>
                                <EureButtonPrimary
                                    type="submit"
                                    loading={processing}
                                >
                                    Guardar
                                </EureButtonPrimary>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

export default function EstructuraAcademica({
    niveles,
    aniosLectivos,
    cursos,
    divisiones,
    turnos,
    planesEstudio,
}: Props) {
    const [tipoCatalogo, setTipoCatalogo] = useState<TipoCatalogo>('curso');
    const [catalogoEditando, setCatalogoEditando] =
        useState<CatalogoConNivel | null>(null);
    const [dialogoCatalogoAbierto, setDialogoCatalogoAbierto] = useState(false);
    const [anioEditando, setAnioEditando] = useState<AnioLectivo | null>(null);
    const [dialogoAnioAbierto, setDialogoAnioAbierto] = useState(false);
    const confirmar = EureConfirmSwal();
    const configuracion = CONFIGURACION_CATALOGOS[tipoCatalogo];

    const catalogos: Record<TipoCatalogo, CatalogoConNivel[]> = {
        curso: cursos,
        division: divisiones,
        turno: turnos,
        plan: planesEstudio,
    };

    const columnasAnios = useMemo<ColumnDef<AnioLectivo>[]>(
        () => [
            {
                id: 'id',
                header: '#',
                accessorFn: (anio) => anio.id,
                cell: ({ row }) => (
                    <div className="text-center tabular-nums">
                        {row.original.id}
                    </div>
                ),
            },
            {
                id: 'anio',
                header: 'Año',
                accessorFn: (anio) => anio.anio,
                cell: ({ row }) => (
                    <div className="text-center font-semibold tabular-nums">
                        {row.original.anio}
                    </div>
                ),
            },
            {
                id: 'estado',
                header: 'Estado académico',
                accessorFn: (anio) => ETIQUETAS_ESTADO[anio.estado],
                cell: ({ row }) => (
                    <Badge
                        variant={
                            row.original.estado === 'vigente'
                                ? 'default'
                                : 'outline'
                        }
                    >
                        {ETIQUETAS_ESTADO[row.original.estado]}
                    </Badge>
                ),
            },
            {
                id: 'grupos',
                header: 'Grupos',
                accessorFn: (anio) => anio.grupos_count ?? 0,
                cell: ({ row }) => (
                    <div className="text-center tabular-nums">
                        {row.original.grupos_count ?? 0}
                    </div>
                ),
            },
            {
                id: 'activo',
                header: 'Registro',
                accessorFn: (anio) => (anio.activo ? 'Activo' : 'Inactivo'),
                cell: ({ row }) => (
                    <EstadoActivo activo={row.original.activo} />
                ),
            },
        ],
        [],
    );

    const accionesAnios: EureDataTableAccion<AnioLectivo>[] = [
        {
            label: 'Editar',
            icon: Edit3Icon,
            hidden: (anio) =>
                anio.estado !== 'preparacion' || (anio.grupos_count ?? 0) > 0,
            onClick: (anio) => {
                setAnioEditando(anio);
                setDialogoAnioAbierto(true);
            },
        },
        {
            label: 'Marcar vigente',
            icon: CheckCircle2Icon,
            color: 'green',
            hidden: (anio) => !anio.activo || anio.estado !== 'preparacion',
            onClick: async (anio) => {
                if (
                    await confirmar({
                        title: `Marcar ${anio.anio} como vigente`,
                        text: 'El año vigente anterior quedará cerrado.',
                        confirmText: 'Marcar vigente',
                    })
                ) {
                    router.patch(
                        marcarVigente.url(anio.id),
                        {},
                        { preserveScroll: true },
                    );
                }
            },
        },
        {
            label: 'Desactivar',
            icon: PowerIcon,
            color: 'red',
            hidden: (anio) =>
                !anio.activo ||
                anio.estado === 'vigente' ||
                (anio.grupos_count ?? 0) > 0,
            onClick: async (anio) => {
                if (
                    await confirmar({
                        title: `Desactivar el año ${anio.anio}`,
                        confirmText: 'Desactivar',
                    })
                ) {
                    router.patch(
                        desactivarAnio.url(anio.id),
                        {},
                        { preserveScroll: true },
                    );
                }
            },
        },
        {
            label: 'Reactivar',
            icon: RotateCcwIcon,
            color: 'green',
            hidden: (anio) => anio.activo,
            onClick: (anio) =>
                router.patch(
                    reactivarAnio.url(anio.id),
                    {},
                    { preserveScroll: true },
                ),
        },
    ];

    const columnasCatalogo = useMemo<ColumnDef<CatalogoConNivel>[]>(() => {
        const columnas: ColumnDef<CatalogoConNivel>[] = [
            {
                id: 'id',
                header: '#',
                accessorFn: (item) => item.id,
                cell: ({ row }) => (
                    <div className="text-center tabular-nums">
                        {row.original.id}
                    </div>
                ),
            },
            {
                id: 'nivel',
                header: 'Nivel',
                accessorFn: (item) => item.nivel?.descripcion ?? '',
            },
        ];

        if (configuracion.usaCodigo) {
            columnas.push({
                id: 'codigo',
                header: 'Código',
                accessorFn: (item) => item.codigo ?? '',
                cell: ({ row }) => (
                    <span className="font-mono text-xs">
                        {row.original.codigo}
                    </span>
                ),
            });
        }

        columnas.push({
            id: 'descripcion',
            header: 'Descripción',
            accessorFn: (item) => item.descripcion,
        });

        if (!configuracion.usaCodigo) {
            columnas.push({
                id: 'orden',
                header: 'Orden',
                accessorFn: (item) => item.orden ?? 0,
                cell: ({ row }) => (
                    <div className="text-center tabular-nums">
                        {row.original.orden}
                    </div>
                ),
            });
        }

        columnas.push(
            {
                id: 'grupos',
                header: 'Grupos',
                accessorFn: (item) => item.grupos_count ?? 0,
                cell: ({ row }) => (
                    <div className="text-center tabular-nums">
                        {row.original.grupos_count ?? 0}
                    </div>
                ),
            },
            {
                id: 'activo',
                header: 'Estado',
                accessorFn: (item) => (item.activo ? 'Activo' : 'Inactivo'),
                cell: ({ row }) => (
                    <EstadoActivo activo={row.original.activo} />
                ),
            },
        );

        return columnas;
    }, [configuracion.usaCodigo]);

    const accionesCatalogo: EureDataTableAccion<CatalogoConNivel>[] = [
        {
            label: 'Editar',
            icon: Edit3Icon,
            onClick: (registro) => {
                setCatalogoEditando(registro);
                setDialogoCatalogoAbierto(true);
            },
        },
        {
            label: 'Desactivar',
            icon: PowerIcon,
            color: 'red',
            hidden: (registro) =>
                !registro.activo || (registro.grupos_count ?? 0) > 0,
            onClick: async (registro) => {
                if (
                    await confirmar({
                        title: `Desactivar ${configuracion.singular}`,
                        text: registro.descripcion,
                        confirmText: 'Desactivar',
                    })
                ) {
                    router.patch(
                        configuracion.desactivar(registro.id),
                        {},
                        { preserveScroll: true },
                    );
                }
            },
        },
        {
            label: 'Reactivar',
            icon: RotateCcwIcon,
            color: 'green',
            hidden: (registro) => registro.activo,
            onClick: (registro) =>
                router.patch(
                    configuracion.reactivar(registro.id),
                    {},
                    { preserveScroll: true },
                ),
        },
    ];

    const opcionesCatalogo: EureSelectOption[] = (
        Object.keys(CONFIGURACION_CATALOGOS) as TipoCatalogo[]
    ).map((tipo) => ({
        value: tipo,
        label: CONFIGURACION_CATALOGOS[tipo].plural,
    }));

    return (
        <>
            <Head title="Estructura académica" />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Estructura académica
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Configurá los años lectivos y los catálogos propios de
                        Primaria y Secundaria.
                    </p>
                </div>

                <EureCard title="Años lectivos">
                    <EureDataTable
                        columns={columnasAnios}
                        data={aniosLectivos}
                        acciones={accionesAnios}
                        globalFilterPlaceholder="Buscar en años..."
                        initialPageSize={5}
                        toolbarActions={
                            <EureButtonPrimary
                                onClick={() => {
                                    setAnioEditando(null);
                                    setDialogoAnioAbierto(true);
                                }}
                            >
                                <PlusIcon className="size-4" />
                                Nuevo año
                            </EureButtonPrimary>
                        }
                    />
                </EureCard>

                <EureCard title="Catálogos por nivel">
                    <div className="mb-4 max-w-sm">
                        <EureSelect
                            options={opcionesCatalogo}
                            value={
                                opcionesCatalogo.find(
                                    (opcion) => opcion.value === tipoCatalogo,
                                ) ?? null
                            }
                            onChange={(opcion) => {
                                setTipoCatalogo(
                                    (opcion?.value as TipoCatalogo) ?? 'curso',
                                );
                                setCatalogoEditando(null);
                            }}
                            placeholder="Seleccionar catálogo"
                        />
                    </div>
                    <EureDataTable
                        columns={columnasCatalogo}
                        data={catalogos[tipoCatalogo]}
                        acciones={accionesCatalogo}
                        globalFilterPlaceholder="Buscar en resultados..."
                        toolbarActions={
                            <EureButtonPrimary
                                onClick={() => {
                                    setCatalogoEditando(null);
                                    setDialogoCatalogoAbierto(true);
                                }}
                            >
                                <PlusIcon className="size-4" />
                                Nuevo {configuracion.singular}
                            </EureButtonPrimary>
                        }
                    />
                </EureCard>
            </div>

            {dialogoAnioAbierto && (
                <DialogoAnioLectivo
                    abierto
                    onOpenChange={setDialogoAnioAbierto}
                    registro={anioEditando}
                />
            )}
            {dialogoCatalogoAbierto && (
                <DialogoCatalogo
                    abierto
                    onOpenChange={setDialogoCatalogoAbierto}
                    tipo={tipoCatalogo}
                    registro={catalogoEditando}
                    niveles={niveles}
                />
            )}
        </>
    );
}

EstructuraAcademica.layout = {
    breadcrumbs: [
        {
            title: 'Estructura académica',
            href: estructuraIndex(),
        },
    ],
};
