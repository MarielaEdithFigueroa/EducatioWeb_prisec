import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    CheckIcon,
    EyeIcon,
    LayersIcon,
    RefreshCwIcon,
    UserPlusIcon,
    UsersIcon,
} from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import { formatId } from '@/lib/utils';
import { index, panel, show } from '@/routes/academico/grupos';
import {
    create as asignarAlumnos,
    index as consultarAlumnos,
} from '@/routes/academico/grupos/alumnos';
import AlumnosAsignados from './alumnos-asignados';
import type { AlumnoAsignado } from './alumnos-asignados';

type GrupoPanel = {
    id: number;
    activo: boolean;
    ciclo_lectivo: number;
    descripcion: string;
    nivel: string;
    curso_division: string;
    turno: string;
};
type Props = { grupos: GrupoPanel[]; cicloInicial: number };
type Respuesta = { grupo_id: number; alumnos: AlumnoAsignado[] };
type Detalle =
    | { estado: 'vacio' | 'cargando' }
    | { estado: 'error'; mensaje: string }
    | { estado: 'listo'; alumnos: AlumnoAsignado[] };

export default function PanelGrupos({ grupos, cicloInicial }: Props) {
    const [ciclo, setCiclo] = useState(String(cicloInicial));
    const [buscar, setBuscar] = useState('');
    const [estado, setEstado] = useState('activos');
    const [seleccionado, setSeleccionado] = useState<GrupoPanel | null>(null);
    const [detalle, setDetalle] = useState<Detalle>({ estado: 'vacio' });
    const version = useRef(0);
    const peticionActual = useRef<AbortController | null>(null);

    useEffect(
        () => () => {
            version.current++;
            peticionActual.current?.abort();
        },
        [],
    );

    const ciclos = useMemo(
        () =>
            Array.from(
                new Set([
                    cicloInicial,
                    ...grupos.map((grupo) => grupo.ciclo_lectivo),
                ]),
            )
                .sort((a, b) => b - a)
                .map((anio) => ({ value: String(anio), label: String(anio) })),
        [grupos, cicloInicial],
    );
    const estados = [
        { value: 'activos', label: 'Activos' },
        { value: 'todos', label: 'Todos' },
        { value: 'inactivos', label: 'Inactivos' },
    ];
    const delCiclo = grupos.filter(
        (grupo) => String(grupo.ciclo_lectivo) === ciclo,
    );
    const visibles = delCiclo.filter(
        (grupo) =>
            (estado === 'todos' || grupo.activo === (estado === 'activos')) &&
            `${formatId(grupo.id, 4)} ${grupo.descripcion}`
                .toLocaleLowerCase('es-AR')
                .includes(buscar.trim().toLocaleLowerCase('es-AR')),
    );
    const limpiarDetalle = () => {
        version.current++;
        peticionActual.current?.abort();
        setSeleccionado(null);
        setDetalle({ estado: 'vacio' });
    };
    const cargar = async (grupo: GrupoPanel) => {
        const peticion = ++version.current;
        peticionActual.current?.abort();
        const controlador = new AbortController();
        peticionActual.current = controlador;
        setSeleccionado(grupo);
        setDetalle({ estado: 'cargando' });
        let mensaje = 'No se pudieron cargar los alumnos. Intentá nuevamente.';

        try {
            const respuestaHttp = await fetch(consultarAlumnos.url(grupo.id), {
                headers: { Accept: 'application/json' },
                signal: controlador.signal,
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (respuestaHttp.redirected || respuestaHttp.status === 401) {
                mensaje =
                    'La sesión venció. Volvé a cargar la pantalla para ingresar.';

                throw new Error('Sesión finalizada');
            }

            if (respuestaHttp.status === 403) {
                mensaje = 'No tenés acceso a los alumnos de este grupo.';
            }

            if (!respuestaHttp.ok) {
                throw new Error('No se pudo consultar el grupo');
            }

            const respuesta: Respuesta = await respuestaHttp.json();

            if (peticion !== version.current || controlador.signal.aborted) {
                return;
            }

            if (
                respuesta.grupo_id !== grupo.id ||
                !Array.isArray(respuesta.alumnos)
            ) {
                throw new Error('Respuesta de otro grupo');
            }

            setDetalle({ estado: 'listo', alumnos: respuesta.alumnos });
        } catch {
            if (peticion === version.current && !controlador.signal.aborted) {
                setDetalle({ estado: 'error', mensaje });
            }
        }
    };

    return (
        <>
            <Head title="Alumnos por grupo" />
            <div className="flex flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-semibold">
                            <LayersIcon
                                aria-hidden="true"
                                className="size-6 text-educatio-icon"
                            />
                            Alumnos por grupo
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Elegí un año lectivo y un grupo para consultar sus
                            alumnos actuales.
                        </p>
                    </div>
                    <EureButtonSecondary asChild>
                        <Link href={index({ query: { ciclo_lectivo: ciclo } })}>
                            <ArrowLeftIcon
                                aria-hidden="true"
                                className="size-4"
                            />
                            Listado de grupos
                        </Link>
                    </EureButtonSecondary>
                </EducatioCard>
                <div className="grid items-start gap-4 lg:grid-cols-[19rem_minmax(0,1fr)]">
                    <aside
                        aria-label="Seleccionar grupo"
                        className="min-w-0 lg:sticky lg:top-20"
                    >
                        <EducatioCard className="space-y-3">
                            <EureSelectGroup
                                label="Año lectivo"
                                name="ciclo_panel"
                                autoFocus
                                options={ciclos}
                                value={
                                    ciclos.find(
                                        (opcion) => opcion.value === ciclo,
                                    ) ?? null
                                }
                                onChange={(opcion) => {
                                    limpiarDetalle();
                                    setCiclo(
                                        String(opcion?.value ?? cicloInicial),
                                    );
                                }}
                            />
                            <EureInputGroup
                                label="Buscar grupo"
                                name="buscar_grupo"
                                placeholder="Curso, división, turno o número"
                                value={buscar}
                                onChange={(event) => {
                                    limpiarDetalle();
                                    setBuscar(event.target.value);
                                }}
                            />
                            <EureSelectGroup
                                label="Estado de los grupos"
                                name="estado_panel"
                                options={estados}
                                value={
                                    estados.find(
                                        (opcion) => opcion.value === estado,
                                    ) ?? null
                                }
                                onChange={(opcion) => {
                                    limpiarDetalle();
                                    setEstado(
                                        String(opcion?.value ?? 'activos'),
                                    );
                                }}
                            />
                            <p
                                role="status"
                                className="text-sm text-muted-foreground"
                            >
                                {visibles.length} de {delCiclo.length} grupos
                                del año {ciclo} ·{' '}
                                {
                                    estados.find(
                                        (opcion) => opcion.value === estado,
                                    )?.label
                                }
                            </p>
                            {visibles.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    No hay grupos con estos filtros.
                                </p>
                            ) : (
                                <ul
                                    className="max-h-64 space-y-0.5 overflow-y-auto p-1 lg:max-h-[calc(100dvh-24rem)]"
                                    aria-label={`Grupos del año ${ciclo}`}
                                >
                                    {visibles.map((grupo) => (
                                        <li key={grupo.id}>
                                            <button
                                                type="button"
                                                aria-pressed={
                                                    seleccionado?.id ===
                                                    grupo.id
                                                }
                                                aria-controls="alumnos-del-grupo"
                                                onClick={() =>
                                                    void cargar(grupo)
                                                }
                                                className={`w-full rounded-sm border px-2 py-1.5 text-left focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none ${seleccionado?.id === grupo.id ? 'border-primary bg-primary/10 text-foreground' : 'border-border hover:bg-muted'}`}
                                            >
                                                <span className="flex items-center justify-between gap-2">
                                                    <strong className="text-sm leading-4">
                                                        {grupo.curso_division}
                                                    </strong>
                                                    <span className="flex shrink-0 items-center gap-1 text-xs tabular-nums">
                                                        #{formatId(grupo.id, 4)}
                                                        <CheckIcon
                                                            aria-hidden="true"
                                                            className={`size-3.5 text-primary ${seleccionado?.id === grupo.id ? '' : 'invisible'}`}
                                                        />
                                                    </span>
                                                </span>
                                                <span className="mt-0.5 block text-xs leading-4 text-muted-foreground">
                                                    {grupo.nivel} ·{' '}
                                                    {grupo.turno}
                                                    {!grupo.activo &&
                                                        ' · Inactivo'}
                                                </span>
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </EducatioCard>
                    </aside>
                    <section
                        id="alumnos-del-grupo"
                        aria-label="Alumnos del grupo seleccionado"
                        aria-busy={detalle.estado === 'cargando'}
                        className="min-w-0"
                    >
                        <EducatioCard className="min-w-0">
                            {seleccionado === null ? (
                                <div className="flex min-h-48 flex-col items-center justify-center gap-3 text-center text-muted-foreground">
                                    <UsersIcon
                                        aria-hidden="true"
                                        className="size-8 text-educatio-icon"
                                    />
                                    <p>
                                        Seleccioná un grupo de la lista para ver
                                        sus alumnos.
                                    </p>
                                </div>
                            ) : (
                                <>
                                    <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <h2 className="flex flex-wrap items-center gap-2 text-lg font-semibold">
                                                {seleccionado.curso_division}
                                                <Badge variant="outline">
                                                    {seleccionado.activo
                                                        ? 'Activo'
                                                        : 'Inactivo'}
                                                </Badge>
                                            </h2>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {seleccionado.descripcion} · #
                                                {formatId(seleccionado.id, 4)}
                                            </p>
                                        </div>
                                        <div className="flex flex-wrap gap-2">
                                            <EureButtonSecondary
                                                type="button"
                                                onClick={() =>
                                                    void cargar(seleccionado)
                                                }
                                                disabled={
                                                    detalle.estado ===
                                                    'cargando'
                                                }
                                                aria-label="Actualizar alumnos"
                                            >
                                                <RefreshCwIcon
                                                    aria-hidden="true"
                                                    className="size-4"
                                                />
                                            </EureButtonSecondary>
                                            <EureButtonSecondary asChild>
                                                <Link
                                                    href={show(seleccionado.id)}
                                                >
                                                    <EyeIcon
                                                        aria-hidden="true"
                                                        className="size-4"
                                                    />
                                                    Ver grupo
                                                </Link>
                                            </EureButtonSecondary>
                                            {seleccionado.activo && (
                                                <EureButtonPrimary
                                                    onClick={() =>
                                                        router.visit(
                                                            asignarAlumnos(
                                                                seleccionado.id,
                                                            ),
                                                        )
                                                    }
                                                >
                                                    <UserPlusIcon
                                                        aria-hidden="true"
                                                        className="size-4"
                                                    />
                                                    Asignar alumnos
                                                </EureButtonPrimary>
                                            )}
                                        </div>
                                    </div>
                                    {detalle.estado === 'cargando' && (
                                        <p
                                            role="status"
                                            className="py-8 text-sm text-muted-foreground"
                                        >
                                            Cargando alumnos…
                                        </p>
                                    )}
                                    {detalle.estado === 'error' && (
                                        <div
                                            role="alert"
                                            className="space-y-3 py-4"
                                        >
                                            <p className="text-sm text-eure-error">
                                                {detalle.mensaje}
                                            </p>
                                            <EureButtonSecondary
                                                onClick={() =>
                                                    void cargar(seleccionado)
                                                }
                                            >
                                                Reintentar
                                            </EureButtonSecondary>
                                            <EureButtonSecondary asChild>
                                                <Link
                                                    href={panel({
                                                        query: {
                                                            ciclo_lectivo:
                                                                ciclo,
                                                        },
                                                    })}
                                                >
                                                    Volver a cargar la pantalla
                                                </Link>
                                            </EureButtonSecondary>
                                        </div>
                                    )}
                                    {detalle.estado === 'listo' && (
                                        <>
                                            <p
                                                role="status"
                                                className="mb-3 text-sm font-medium"
                                            >
                                                Alumnos actuales:{' '}
                                                {detalle.alumnos.length}
                                            </p>
                                            {detalle.alumnos.length === 0 ? (
                                                <p className="py-6 text-sm text-muted-foreground">
                                                    Este grupo no tiene alumnos
                                                    asignados actualmente.
                                                </p>
                                            ) : (
                                                <AlumnosAsignados
                                                    key={seleccionado.id}
                                                    alumnos={detalle.alumnos}
                                                />
                                            )}
                                        </>
                                    )}
                                </>
                            )}
                        </EducatioCard>
                    </section>
                </div>
            </div>
        </>
    );
}

PanelGrupos.layout = () => ({
    breadcrumbs: [
        { title: 'Grupos', href: index.url() },
        { title: 'Alumnos por grupo', href: panel.url() },
    ],
});
