import { Form, Head, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    CopyPlusIcon,
    Edit3Icon,
    FilterIcon,
    PlusIcon,
    PowerIcon,
    RotateCcwIcon,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';
import {
    desactivar,
    reactivar,
    store as crearGrupo,
    update as actualizarGrupo,
} from '@/actions/App/Http/Controllers/Academico/GrupoController';
import ProyectarGruposController from '@/actions/App/Http/Controllers/Academico/ProyectarGruposController';
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
    EureSelectGroup,
} from '@/components/ui/eure';
import type {
    EureDataTableAccion,
    EureSelectOption,
} from '@/components/ui/eure';
import { index as gruposIndex } from '@/routes/academico/grupos';
import type {
    AnioLectivo,
    CatalogoConNivel,
    FiltrosGrupos,
    GrupoAcademico,
    Nivel,
} from '@/types';

type Props = {
    grupos: GrupoAcademico[];
    filtros: FiltrosGrupos;
    niveles: Nivel[];
    aniosLectivos: AnioLectivo[];
    cursos: CatalogoConNivel[];
    divisiones: CatalogoConNivel[];
    turnos: CatalogoConNivel[];
    planesEstudio: CatalogoConNivel[];
};

const ETIQUETAS_ESTADO = {
    preparacion: 'En preparación',
    vigente: 'Vigente',
    cerrado: 'Cerrado',
} as const;

const OPCIONES_ESTADO: EureSelectOption[] = [
    { value: 'activos', label: 'Activos' },
    { value: 'inactivos', label: 'Inactivos' },
    { value: 'todos', label: 'Todos' },
];

function opcionSeleccionada(
    opciones: EureSelectOption[],
    valor: number | string | null,
) {
    return opciones.find((opcion) => opcion.value === valor) ?? null;
}

function opcionesCatalogo(
    registros: CatalogoConNivel[],
    nivelId: number | null,
    seleccionadoId: number | null,
    mostrarCodigo = false,
): EureSelectOption[] {
    return registros
        .filter((registro) => registro.nivel_id === nivelId)
        .filter((registro) => registro.activo || registro.id === seleccionadoId)
        .map((registro) => ({
            value: registro.id,
            label: `${mostrarCodigo && registro.codigo ? `${registro.codigo} · ` : ''}${registro.descripcion}${registro.activo ? '' : ' (inactivo)'}`,
        }));
}

function DialogoGrupo({
    abierto,
    onOpenChange,
    grupo,
    niveles,
    aniosLectivos,
    cursos,
    divisiones,
    turnos,
    planesEstudio,
}: {
    abierto: boolean;
    onOpenChange: (abierto: boolean) => void;
    grupo: GrupoAcademico | null;
    niveles: Nivel[];
    aniosLectivos: AnioLectivo[];
    cursos: CatalogoConNivel[];
    divisiones: CatalogoConNivel[];
    turnos: CatalogoConNivel[];
    planesEstudio: CatalogoConNivel[];
}) {
    const [anioId, setAnioId] = useState<number | null>(
        grupo?.anio_lectivo_id ??
            aniosLectivos.find((anio) => anio.estado === 'vigente')?.id ??
            null,
    );
    const [nivelId, setNivelId] = useState<number | null>(
        grupo?.curso.nivel_id ?? niveles[0]?.id ?? null,
    );
    const [planId, setPlanId] = useState<number | null>(
        grupo?.plan_estudio_id ?? null,
    );
    const [cursoId, setCursoId] = useState<number | null>(
        grupo?.curso_id ?? null,
    );
    const [divisionId, setDivisionId] = useState<number | null>(
        grupo?.division_id ?? null,
    );
    const [turnoId, setTurnoId] = useState<number | null>(
        grupo?.turno_id ?? null,
    );

    const opcionesAnio: EureSelectOption[] = aniosLectivos
        .filter(
            (anio) =>
                (anio.activo && anio.estado !== 'cerrado') ||
                anio.id === anioId,
        )
        .map((anio) => ({
            value: anio.id,
            label: `${anio.anio} · ${ETIQUETAS_ESTADO[anio.estado]}${anio.activo ? '' : ' (inactivo)'}`,
        }));
    const opcionesNivel: EureSelectOption[] = niveles.map((nivel) => ({
        value: nivel.id,
        label: nivel.descripcion,
    }));
    const opcionesPlan = opcionesCatalogo(planesEstudio, nivelId, planId, true);
    const opcionesCurso = opcionesCatalogo(cursos, nivelId, cursoId);
    const opcionesDivision = opcionesCatalogo(divisiones, nivelId, divisionId);
    const opcionesTurno = opcionesCatalogo(turnos, nivelId, turnoId);
    const formulario = grupo
        ? actualizarGrupo.form(grupo.id)
        : crearGrupo.form();

    const cambiarNivel = (opcion: EureSelectOption | null) => {
        setNivelId(Number(opcion?.value) || null);
        setPlanId(null);
        setCursoId(null);
        setDivisionId(null);
        setTurnoId(null);
    };

    return (
        <Dialog open={abierto} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>
                        {grupo ? 'Editar grupo' : 'Nuevo grupo'}
                    </DialogTitle>
                    <DialogDescription>
                        El plan, curso, división y turno deben pertenecer al
                        mismo nivel.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    key={`${grupo?.id ?? 'nuevo'}-${abierto}`}
                    {...formulario}
                    options={{ preserveScroll: true }}
                    onSuccess={() => onOpenChange(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <input
                                type="hidden"
                                name="anio_lectivo_id"
                                value={anioId ?? ''}
                            />
                            <input
                                type="hidden"
                                name="plan_estudio_id"
                                value={planId ?? ''}
                            />
                            <input
                                type="hidden"
                                name="curso_id"
                                value={cursoId ?? ''}
                            />
                            <input
                                type="hidden"
                                name="division_id"
                                value={divisionId ?? ''}
                            />
                            <input
                                type="hidden"
                                name="turno_id"
                                value={turnoId ?? ''}
                            />

                            <div className="grid gap-4 md:grid-cols-2">
                                <EureSelectGroup
                                    label="Año lectivo"
                                    name="anio_lectivo_selector"
                                    value={opcionSeleccionada(
                                        opcionesAnio,
                                        anioId,
                                    )}
                                    onChange={(opcion) =>
                                        setAnioId(Number(opcion?.value) || null)
                                    }
                                    options={opcionesAnio}
                                    error={Boolean(errors.anio_lectivo_id)}
                                    errorMessage={errors.anio_lectivo_id}
                                    required
                                />
                                <EureSelectGroup
                                    label="Nivel"
                                    name="nivel_selector"
                                    value={opcionSeleccionada(
                                        opcionesNivel,
                                        nivelId,
                                    )}
                                    onChange={cambiarNivel}
                                    options={opcionesNivel}
                                    error={Boolean(errors.nivel_id)}
                                    errorMessage={errors.nivel_id}
                                    required
                                />
                                <EureSelectGroup
                                    label="Plan de estudio"
                                    name="plan_selector"
                                    value={opcionSeleccionada(
                                        opcionesPlan,
                                        planId,
                                    )}
                                    onChange={(opcion) =>
                                        setPlanId(Number(opcion?.value) || null)
                                    }
                                    options={opcionesPlan}
                                    error={Boolean(errors.plan_estudio_id)}
                                    errorMessage={errors.plan_estudio_id}
                                    required
                                />
                                <EureSelectGroup
                                    label="Curso"
                                    name="curso_selector"
                                    value={opcionSeleccionada(
                                        opcionesCurso,
                                        cursoId,
                                    )}
                                    onChange={(opcion) =>
                                        setCursoId(
                                            Number(opcion?.value) || null,
                                        )
                                    }
                                    options={opcionesCurso}
                                    error={Boolean(errors.curso_id)}
                                    errorMessage={errors.curso_id}
                                    required
                                />
                                <EureSelectGroup
                                    label="División"
                                    name="division_selector"
                                    value={opcionSeleccionada(
                                        opcionesDivision,
                                        divisionId,
                                    )}
                                    onChange={(opcion) =>
                                        setDivisionId(
                                            Number(opcion?.value) || null,
                                        )
                                    }
                                    options={opcionesDivision}
                                    error={Boolean(errors.division_id)}
                                    errorMessage={errors.division_id}
                                    required
                                />
                                <EureSelectGroup
                                    label="Turno"
                                    name="turno_selector"
                                    value={opcionSeleccionada(
                                        opcionesTurno,
                                        turnoId,
                                    )}
                                    onChange={(opcion) =>
                                        setTurnoId(
                                            Number(opcion?.value) || null,
                                        )
                                    }
                                    options={opcionesTurno}
                                    error={Boolean(errors.turno_id)}
                                    errorMessage={errors.turno_id}
                                    required
                                />
                            </div>

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

function DialogoProyeccion({
    abierto,
    onOpenChange,
    aniosLectivos,
}: {
    abierto: boolean;
    onOpenChange: (abierto: boolean) => void;
    aniosLectivos: AnioLectivo[];
}) {
    const vigente = aniosLectivos.find(
        (anio) => anio.activo && anio.estado === 'vigente',
    );
    const origenInicial =
        vigente ?? aniosLectivos.find((anio) => anio.activo) ?? null;
    const destinoInicial = aniosLectivos.find(
        (anio) =>
            anio.activo &&
            anio.estado === 'preparacion' &&
            anio.anio > (origenInicial?.anio ?? 0),
    );
    const [origenId, setOrigenId] = useState<number | null>(
        origenInicial?.id ?? null,
    );
    const [destinoId, setDestinoId] = useState<number | null>(
        destinoInicial?.id ?? null,
    );

    const origen = aniosLectivos.find((anio) => anio.id === origenId) ?? null;
    const opcionesOrigen: EureSelectOption[] = aniosLectivos
        .filter((anio) => anio.activo)
        .map((anio) => ({
            value: anio.id,
            label: `${anio.anio} · ${ETIQUETAS_ESTADO[anio.estado]}`,
        }));
    const opcionesDestino: EureSelectOption[] = aniosLectivos
        .filter(
            (anio) =>
                anio.activo &&
                anio.estado === 'preparacion' &&
                anio.anio > (origen?.anio ?? 0),
        )
        .map((anio) => ({
            value: anio.id,
            label: `${anio.anio} · En preparación`,
        }));

    return (
        <Dialog open={abierto} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Preparar el próximo año</DialogTitle>
                    <DialogDescription>
                        Se copiarán los grupos activos. Los existentes se omiten
                        o reactivan, sin modificar grupos manuales.
                    </DialogDescription>
                </DialogHeader>

                {origenId ? (
                    <Form
                        key={`${origenId}-${destinoId ?? 'sin-destino'}-${abierto}`}
                        {...ProyectarGruposController.form(origenId)}
                        options={{ preserveScroll: true }}
                        onSuccess={() => onOpenChange(false)}
                        className="grid gap-4"
                    >
                        {({ errors, processing }) => (
                            <>
                                <input
                                    type="hidden"
                                    name="destino_id"
                                    value={destinoId ?? ''}
                                />
                                <EureSelectGroup
                                    label="Año de origen"
                                    name="origen_selector"
                                    value={opcionSeleccionada(
                                        opcionesOrigen,
                                        origenId,
                                    )}
                                    onChange={(opcion) => {
                                        setOrigenId(
                                            Number(opcion?.value) || null,
                                        );
                                        setDestinoId(null);
                                    }}
                                    options={opcionesOrigen}
                                    error={Boolean(errors.anio_lectivo_id)}
                                    errorMessage={errors.anio_lectivo_id}
                                    required
                                />
                                <EureSelectGroup
                                    label="Año de destino"
                                    name="destino_selector"
                                    value={opcionSeleccionada(
                                        opcionesDestino,
                                        destinoId,
                                    )}
                                    onChange={(opcion) =>
                                        setDestinoId(
                                            Number(opcion?.value) || null,
                                        )
                                    }
                                    options={opcionesDestino}
                                    error={Boolean(errors.destino_id)}
                                    errorMessage={errors.destino_id}
                                    required
                                />
                                <EureAlert
                                    type="info"
                                    message={`El origen tiene ${origen?.grupos_activos_count ?? 0} grupos activos para evaluar.`}
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
                                        disabled={!destinoId}
                                    >
                                        Proyectar grupos
                                    </EureButtonPrimary>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                ) : (
                    <EureAlert
                        type="warning"
                        message="No hay un año activo que pueda usarse como origen."
                    />
                )}
            </DialogContent>
        </Dialog>
    );
}

export default function Grupos({
    grupos,
    filtros,
    niveles,
    aniosLectivos,
    cursos,
    divisiones,
    turnos,
    planesEstudio,
}: Props) {
    const [anioFiltro, setAnioFiltro] = useState<number | null>(
        filtros.anio_lectivo_id,
    );
    const [nivelFiltro, setNivelFiltro] = useState<number | null>(
        filtros.nivel_id,
    );
    const [estadoFiltro, setEstadoFiltro] = useState<FiltrosGrupos['estado']>(
        filtros.estado,
    );
    const [grupoEditando, setGrupoEditando] = useState<GrupoAcademico | null>(
        null,
    );
    const [dialogoGrupoAbierto, setDialogoGrupoAbierto] = useState(false);
    const [dialogoProyeccionAbierto, setDialogoProyeccionAbierto] =
        useState(false);
    const confirmar = EureConfirmSwal();

    const opcionesAnioFiltro: EureSelectOption[] = aniosLectivos.map(
        (anio) => ({
            value: anio.id,
            label: `${anio.anio} · ${ETIQUETAS_ESTADO[anio.estado]}${anio.activo ? '' : ' (inactivo)'}`,
        }),
    );
    const opcionesNivelFiltro: EureSelectOption[] = niveles.map((nivel) => ({
        value: nivel.id,
        label: nivel.descripcion,
    }));

    const aplicarFiltros = () => {
        router.get(
            gruposIndex.url(),
            {
                anio_lectivo_id: anioFiltro ?? undefined,
                nivel_id: nivelFiltro ?? undefined,
                estado: estadoFiltro,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const limpiarFiltros = () => {
        setAnioFiltro(null);
        setNivelFiltro(null);
        setEstadoFiltro('activos');
        router.get(
            gruposIndex.url(),
            { estado: 'activos' },
            { preserveState: true, replace: true },
        );
    };

    const columnas = useMemo<ColumnDef<GrupoAcademico>[]>(
        () => [
            {
                id: 'id',
                header: '#',
                accessorFn: (grupo) => grupo.id,
                cell: ({ row }) => (
                    <div className="text-center tabular-nums">
                        {row.original.id}
                    </div>
                ),
            },
            {
                id: 'anio',
                header: 'Año',
                accessorFn: (grupo) => grupo.anio_lectivo.anio,
                cell: ({ row }) => (
                    <div className="text-center tabular-nums">
                        {row.original.anio_lectivo.anio}
                    </div>
                ),
            },
            {
                id: 'nivel',
                header: 'Nivel',
                accessorFn: (grupo) => grupo.curso.nivel?.descripcion ?? '',
            },
            {
                id: 'plan',
                header: 'Plan',
                accessorFn: (grupo) =>
                    `${grupo.plan_estudio.codigo ?? ''} ${grupo.plan_estudio.descripcion}`,
                cell: ({ row }) => (
                    <div>
                        <span className="font-medium">
                            {row.original.plan_estudio.descripcion}
                        </span>
                        <span className="ml-2 font-mono text-xs text-muted-foreground">
                            {row.original.plan_estudio.codigo}
                        </span>
                    </div>
                ),
            },
            {
                id: 'curso',
                header: 'Curso',
                accessorFn: (grupo) => grupo.curso.descripcion,
            },
            {
                id: 'division',
                header: 'División',
                accessorFn: (grupo) => grupo.division.descripcion,
            },
            {
                id: 'turno',
                header: 'Turno',
                accessorFn: (grupo) => grupo.turno.descripcion,
            },
            {
                id: 'activo',
                header: 'Estado',
                accessorFn: (grupo) => (grupo.activo ? 'Activo' : 'Inactivo'),
                cell: ({ row }) => (
                    <Badge
                        variant={row.original.activo ? 'default' : 'secondary'}
                    >
                        {row.original.activo ? 'Activo' : 'Inactivo'}
                    </Badge>
                ),
            },
        ],
        [],
    );

    const mostrarErrorAccion = (errors: Record<string, string>) => {
        toast.error(
            Object.values(errors)[0] ?? 'No se pudo completar la operación.',
        );
    };

    const acciones: EureDataTableAccion<GrupoAcademico>[] = [
        {
            label: 'Editar',
            icon: Edit3Icon,
            onClick: (grupo) => {
                setGrupoEditando(grupo);
                setDialogoGrupoAbierto(true);
            },
        },
        {
            label: 'Desactivar',
            icon: PowerIcon,
            color: 'red',
            hidden: (grupo) => !grupo.activo,
            onClick: async (grupo) => {
                if (
                    await confirmar({
                        title: 'Desactivar grupo',
                        text: `${grupo.anio_lectivo.anio} · ${grupo.curso.descripcion} ${grupo.division.descripcion} · ${grupo.turno.descripcion}`,
                        confirmText: 'Desactivar',
                    })
                ) {
                    router.patch(
                        desactivar.url(grupo.id),
                        {},
                        { preserveScroll: true, onError: mostrarErrorAccion },
                    );
                }
            },
        },
        {
            label: 'Reactivar',
            icon: RotateCcwIcon,
            color: 'green',
            hidden: (grupo) => grupo.activo,
            onClick: (grupo) =>
                router.patch(
                    reactivar.url(grupo.id),
                    {},
                    { preserveScroll: true, onError: mostrarErrorAccion },
                ),
        },
    ];

    return (
        <>
            <Head title="Grupos" />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Grupos
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Administrá las combinaciones de año, plan, curso,
                            división y turno.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <EureButtonSecondary
                            onClick={() => setDialogoProyeccionAbierto(true)}
                        >
                            <CopyPlusIcon className="size-4" />
                            Preparar próximo año
                        </EureButtonSecondary>
                        <EureButtonPrimary
                            onClick={() => {
                                setGrupoEditando(null);
                                setDialogoGrupoAbierto(true);
                            }}
                        >
                            <PlusIcon className="size-4" />
                            Nuevo grupo
                        </EureButtonPrimary>
                    </div>
                </div>

                <EureCard title="Filtros">
                    <div className="grid gap-4 md:grid-cols-3 xl:grid-cols-[minmax(13rem,1fr)_minmax(13rem,1fr)_minmax(12rem,1fr)_auto] xl:items-end">
                        <EureSelectGroup
                            label="Año lectivo"
                            name="filtro_anio"
                            value={opcionSeleccionada(
                                opcionesAnioFiltro,
                                anioFiltro,
                            )}
                            onChange={(opcion) =>
                                setAnioFiltro(Number(opcion?.value) || null)
                            }
                            options={opcionesAnioFiltro}
                            placeholder="Todos los años"
                        />
                        <EureSelectGroup
                            label="Nivel"
                            name="filtro_nivel"
                            value={opcionSeleccionada(
                                opcionesNivelFiltro,
                                nivelFiltro,
                            )}
                            onChange={(opcion) =>
                                setNivelFiltro(Number(opcion?.value) || null)
                            }
                            options={opcionesNivelFiltro}
                            placeholder="Todos los niveles"
                        />
                        <EureSelectGroup
                            label="Estado"
                            name="filtro_estado"
                            value={opcionSeleccionada(
                                OPCIONES_ESTADO,
                                estadoFiltro,
                            )}
                            onChange={(opcion) =>
                                setEstadoFiltro(
                                    (opcion?.value as FiltrosGrupos['estado']) ??
                                        'activos',
                                )
                            }
                            options={OPCIONES_ESTADO}
                        />
                        <div className="flex flex-wrap gap-2">
                            <EureButtonPrimary onClick={aplicarFiltros}>
                                <FilterIcon className="size-4" />
                                Aplicar
                            </EureButtonPrimary>
                            <EureButtonSecondary onClick={limpiarFiltros}>
                                Limpiar
                            </EureButtonSecondary>
                        </div>
                    </div>
                </EureCard>

                <EureCard>
                    <EureDataTable
                        columns={columnas}
                        data={grupos}
                        acciones={acciones}
                        globalFilterPlaceholder="Buscar en resultados..."
                        stickyHeader
                    />
                </EureCard>
            </div>

            {dialogoGrupoAbierto && (
                <DialogoGrupo
                    abierto
                    onOpenChange={setDialogoGrupoAbierto}
                    grupo={grupoEditando}
                    niveles={niveles}
                    aniosLectivos={aniosLectivos}
                    cursos={cursos}
                    divisiones={divisiones}
                    turnos={turnos}
                    planesEstudio={planesEstudio}
                />
            )}
            {dialogoProyeccionAbierto && (
                <DialogoProyeccion
                    abierto
                    onOpenChange={setDialogoProyeccionAbierto}
                    aniosLectivos={aniosLectivos}
                />
            )}
        </>
    );
}

Grupos.layout = {
    breadcrumbs: [
        {
            title: 'Grupos',
            href: gruposIndex(),
        },
    ],
};
