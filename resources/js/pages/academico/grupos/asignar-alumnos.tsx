import { Head, Link, router, useForm } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowLeftIcon, FilterIcon, UserPlusIcon, XIcon } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Checkbox } from '@/components/ui/checkbox';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureDataTable from '@/components/ui/eure/EureDataTable';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import { formatCohorteEscolar } from '@/lib/date';
import { formatId } from '@/lib/utils';
import { show as verAlumno } from '@/routes/academico/alumnos';
import { index, show } from '@/routes/academico/grupos';
import { create, store } from '@/routes/academico/grupos/alumnos';

type Candidato = {
    id: number;
    legajo: string;
    apellido: string;
    nombre: string;
    numero_documento: string | null;
    cohorte_nacimiento: number | null;
    ciclo_lectivo_actual: number | null;
    grupo_id: number | null;
    grupo_descripcion: string | null;
    grupo_activo: boolean | null;
    version_historial: string;
};
type Fila = Candidato & { idF: string };
type Seleccion = { alumno_id: number; version_historial: string };
type Props = {
    grupo: { id: number; descripcion: string; activo: boolean };
    alumnos: Candidato[];
};

const columnasDatos: ColumnDef<Fila>[] = [
    {
        accessorKey: 'id',
        header: '#',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => (
            <Link
                href={verAlumno(row.original.id, {
                    query: { seccion: 'grupos' },
                })}
                target="_blank"
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
        id: 'alumno',
        accessorFn: (fila) => `${fila.apellido}, ${fila.nombre}`,
        header: 'Alumno',
        meta: { className: 'text-left' },
    },
    {
        accessorKey: 'numero_documento',
        header: 'Documento',
        meta: { className: 'hidden text-center tabular-nums lg:table-cell' },
        cell: ({ row }) => row.original.numero_documento ?? '—',
    },
    {
        id: 'grupo',
        accessorFn: (fila) =>
            fila.grupo_descripcion
                ? `${fila.grupo_descripcion}${fila.grupo_activo ? '' : ' (inactivo)'}`
                : 'Sin grupo',
        header: 'Grupo actual',
        meta: { className: 'text-left' },
    },
    {
        accessorKey: 'ciclo_lectivo_actual',
        header: 'Año lectivo',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) => row.original.ciclo_lectivo_actual ?? '—',
    },
    {
        accessorKey: 'cohorte_nacimiento',
        header: 'Cohorte nac.',
        meta: { className: 'text-center tabular-nums' },
        cell: ({ row }) =>
            row.original.cohorte_nacimiento === null ? (
                'Sin dato'
            ) : (
                <span
                    title={formatCohorteEscolar(
                        row.original.cohorte_nacimiento,
                    )}
                >
                    {row.original.cohorte_nacimiento}
                </span>
            ),
    },
];

export default function AsignarAlumnos({ grupo, alumnos }: Props) {
    const [buscar, setBuscar] = useState('');
    const [origen, setOrigen] = useState('sin_grupo');
    const [cicloLectivo, setCicloLectivo] = useState('todos');
    const [cohorteNacimiento, setCohorteNacimiento] = useState('todos');
    const [revisando, setRevisando] = useState(false);
    const form = useForm<{ alumnos: Seleccion[]; motivo: string }>({
        alumnos: [],
        motivo: '',
    });
    const seleccionados = new Set(
        form.data.alumnos.map((fila) => fila.alumno_id),
    );
    const filas = useMemo(
        () =>
            alumnos.map((alumno) => ({
                ...alumno,
                idF: formatId(alumno.id, 5),
            })),
        [alumnos],
    );
    const opcionesOrigen = useMemo(
        () => [
            { value: 'sin_grupo', label: 'Sin grupo asignado' },
            { value: 'todos', label: 'Todos los grupos' },
            ...Array.from(
                new Map(
                    alumnos
                        .filter((alumno) => alumno.grupo_id !== null)
                        .map((alumno) => [
                            alumno.grupo_id,
                            {
                                value: String(alumno.grupo_id),
                                label: `${alumno.grupo_descripcion}${alumno.grupo_activo ? '' : ' (inactivo)'}`,
                            },
                        ]),
                ).values(),
            ).sort((a, b) => a.label.localeCompare(b.label, 'es-AR')),
        ],
        [alumnos],
    );
    const texto = buscar.trim().toLocaleLowerCase('es-AR');
    const opcionesCiclo = useMemo(
        () => [
            { value: 'todos', label: 'Todos los años' },
            ...Array.from(
                new Set(
                    alumnos.flatMap((alumno) =>
                        alumno.ciclo_lectivo_actual === null
                            ? []
                            : [alumno.ciclo_lectivo_actual],
                    ),
                ),
            )
                .sort((a, b) => b - a)
                .map((anio) => ({ value: String(anio), label: String(anio) })),
        ],
        [alumnos],
    );
    const opcionesNacimiento = useMemo(
        () => [
            { value: 'todos', label: 'Todas las cohortes' },
            { value: 'sin_dato', label: 'Sin fecha de nacimiento' },
            ...Array.from(
                new Set(
                    alumnos.flatMap((alumno) =>
                        alumno.cohorte_nacimiento === null
                            ? []
                            : [alumno.cohorte_nacimiento],
                    ),
                ),
            )
                .sort((a, b) => b - a)
                .map((anio) => ({
                    value: String(anio),
                    label: formatCohorteEscolar(anio),
                })),
        ],
        [alumnos],
    );
    const filtrosActivos = [
        ...(texto
            ? [
                  {
                      id: 'buscar',
                      label: `Búsqueda: ${buscar.trim()}`,
                      quitar: () => setBuscar(''),
                  },
              ]
            : []),
        ...(origen !== 'todos'
            ? [
                  {
                      id: 'origen',
                      label: `Grupo: ${opcionesOrigen.find((opcion) => opcion.value === origen)?.label}`,
                      quitar: () => setOrigen('todos'),
                  },
              ]
            : []),
        ...(cicloLectivo !== 'todos'
            ? [
                  {
                      id: 'ciclo',
                      label: `Año lectivo: ${cicloLectivo}`,
                      quitar: () => setCicloLectivo('todos'),
                  },
              ]
            : []),
        ...(cohorteNacimiento !== 'todos'
            ? [
                  {
                      id: 'nacimiento',
                      label:
                          cohorteNacimiento === 'sin_dato'
                              ? 'Sin fecha de nacimiento'
                              : `Cohorte de nacimiento: ${formatCohorteEscolar(Number(cohorteNacimiento))}`,
                      quitar: () => setCohorteNacimiento('todos'),
                  },
              ]
            : []),
    ];
    const limpiarFiltros = () => {
        setBuscar('');
        setOrigen('todos');
        setCicloLectivo('todos');
        setCohorteNacimiento('todos');
    };
    const filtrados = filas.filter(
        (alumno) =>
            (origen === 'todos' ||
                (origen === 'sin_grupo'
                    ? alumno.grupo_id === null
                    : String(alumno.grupo_id) === origen)) &&
            (cicloLectivo === 'todos' ||
                String(alumno.ciclo_lectivo_actual) === cicloLectivo) &&
            (cohorteNacimiento === 'todos' ||
                (cohorteNacimiento === 'sin_dato'
                    ? alumno.cohorte_nacimiento === null
                    : String(alumno.cohorte_nacimiento) ===
                      cohorteNacimiento)) &&
            `${alumno.idF} ${alumno.legajo} ${alumno.apellido} ${alumno.nombre} ${alumno.numero_documento ?? ''}`
                .toLocaleLowerCase('es-AR')
                .includes(texto),
    );
    const elegidos = filas.filter((alumno) => seleccionados.has(alumno.id));
    const ingresos = elegidos.filter(
        (alumno) => alumno.grupo_id === null,
    ).length;
    const traslados = elegidos.length - ingresos;
    const ocultos = elegidos.filter(
        (alumno) => !filtrados.some((fila) => fila.id === alumno.id),
    ).length;
    const seleccionables = filtrados.filter(
        (alumno) =>
            alumno.grupo_id !== grupo.id && !seleccionados.has(alumno.id),
    );
    const cambiarSeleccion = (alumno: Fila, seleccionado: boolean) => {
        form.clearErrors();
        form.setData(
            'alumnos',
            seleccionado
                ? [
                      ...form.data.alumnos,
                      {
                          alumno_id: alumno.id,
                          version_historial: alumno.version_historial,
                      },
                  ]
                : form.data.alumnos.filter(
                      (fila) => fila.alumno_id !== alumno.id,
                  ),
        );
    };
    const columnas: ColumnDef<Fila>[] = [
        {
            id: 'seleccion',
            header: 'Elegir',
            enableSorting: false,
            meta: { className: 'text-center' },
            cell: ({ row }) =>
                row.original.grupo_id === grupo.id ? (
                    <span className="text-xs text-muted-foreground">
                        Ya pertenece
                    </span>
                ) : (
                    <Checkbox
                        aria-label={`Seleccionar a ${row.original.apellido}, ${row.original.nombre}, legajo ${row.original.legajo}`}
                        checked={seleccionados.has(row.original.id)}
                        disabled={
                            form.processing ||
                            !grupo.activo ||
                            (!seleccionados.has(row.original.id) &&
                                elegidos.length >= 200)
                        }
                        onCheckedChange={(valor) =>
                            cambiarSeleccion(row.original, valor === true)
                        }
                    />
                ),
        },
        ...columnasDatos,
    ];
    const columnasRevision: ColumnDef<Fila>[] = [
        ...columnasDatos,
        {
            id: 'movimiento',
            header: 'Movimiento',
            accessorFn: (fila) =>
                fila.grupo_id === null ? 'Ingreso' : 'Traslado',
            meta: { className: 'text-left' },
        },
    ];
    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (form.processing || !grupo.activo || elegidos.length === 0) {
            return;
        }

        if (!revisando) {
            setRevisando(true);

            return;
        }

        form.submit(store(grupo.id), { preserveScroll: true });
    };

    return (
        <>
            <Head title={`Asignar alumnos · ${grupo.descripcion}`} />
            <div className="mx-auto flex w-full max-w-7xl flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div className="flex items-center gap-2">
                            <UserPlusIcon
                                aria-hidden="true"
                                className="size-6 text-educatio-icon"
                            />
                            <h1 className="text-xl font-semibold">
                                Asignar alumnos
                            </h1>
                        </div>
                        <p className="mt-1 text-sm">
                            Destino: <strong>{grupo.descripcion}</strong>
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Los traslados cierran el grupo anterior y conservan
                            el historial. Tienen efecto al confirmar.
                        </p>
                    </div>
                    <EureButtonSecondary asChild disabled={form.processing}>
                        <Link href={show(grupo.id)}>
                            <ArrowLeftIcon
                                aria-hidden="true"
                                className="size-4"
                            />
                            Volver al grupo
                        </Link>
                    </EureButtonSecondary>
                </EducatioCard>
                {!grupo.activo && (
                    <p role="alert" className="text-sm text-educatio-warning">
                        Este grupo está inactivo. Reactivalo antes de asignar
                        alumnos.
                    </p>
                )}
                <EducatioCard>
                    <h2 className="mb-3 text-lg font-semibold">
                        {revisando
                            ? 'Revisar movimientos'
                            : 'Seleccionar alumnos'}
                    </h2>
                    {!revisando && (
                        <>
                            <div className="mb-3 grid gap-3 md:grid-cols-2 xl:grid-cols-12">
                                <EureInputGroup
                                    className="xl:col-span-3"
                                    name="buscar"
                                    label="Buscar alumno"
                                    autoFocus
                                    value={buscar}
                                    onChange={(event) =>
                                        setBuscar(event.target.value)
                                    }
                                    placeholder="Apellido, nombre, legajo o documento"
                                    disabled={form.processing}
                                />
                                <EureSelectGroup
                                    className="xl:col-span-3"
                                    name="origen"
                                    label="Grupo actual"
                                    value={
                                        opcionesOrigen.find(
                                            (opcion) => opcion.value === origen,
                                        ) ?? null
                                    }
                                    options={opcionesOrigen}
                                    onChange={(opcion) =>
                                        setOrigen(
                                            String(
                                                opcion?.value ?? 'sin_grupo',
                                            ),
                                        )
                                    }
                                    isDisabled={form.processing}
                                />
                                <EureSelectGroup
                                    className="xl:col-span-2"
                                    name="ciclo_lectivo"
                                    label="Año lectivo"
                                    value={
                                        opcionesCiclo.find(
                                            (opcion) =>
                                                opcion.value === cicloLectivo,
                                        ) ?? null
                                    }
                                    options={opcionesCiclo}
                                    onChange={(opcion) =>
                                        setCicloLectivo(
                                            String(opcion?.value ?? 'todos'),
                                        )
                                    }
                                    helperText="Del grupo actual."
                                    isDisabled={form.processing}
                                />
                                <EureSelectGroup
                                    className="xl:col-span-4"
                                    name="cohorte_nacimiento"
                                    label="Cohorte de nacimiento"
                                    helperText="Corte escolar al 30/06: del 01/07 del año anterior al 30/06 del indicado."
                                    value={
                                        opcionesNacimiento.find(
                                            (opcion) =>
                                                opcion.value ===
                                                cohorteNacimiento,
                                        ) ?? null
                                    }
                                    options={opcionesNacimiento}
                                    onChange={(opcion) =>
                                        setCohorteNacimiento(
                                            String(opcion?.value ?? 'todos'),
                                        )
                                    }
                                    isDisabled={form.processing}
                                />
                            </div>
                            <div
                                className={`mb-3 space-y-2 rounded-sm border p-3 ${filtrosActivos.length > 0 ? 'border-primary/30 bg-primary/5' : 'border-border'}`}
                            >
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <p
                                        role="status"
                                        className="flex flex-wrap items-center gap-2 text-sm"
                                    >
                                        <FilterIcon
                                            aria-hidden="true"
                                            className="size-4 text-educatio-icon"
                                        />
                                        <strong>
                                            {filtrosActivos.length > 0
                                                ? `Filtrando · ${filtrosActivos.length} filtros activos`
                                                : 'Sin filtros activos'}
                                        </strong>
                                        <span>
                                            {filtrados.length} de{' '}
                                            {alumnos.length} alumnos activos
                                        </span>
                                    </p>
                                    <EureButtonSecondary
                                        type="button"
                                        disabled={
                                            form.processing ||
                                            filtrosActivos.length === 0
                                        }
                                        onClick={limpiarFiltros}
                                    >
                                        Limpiar filtros
                                    </EureButtonSecondary>
                                </div>
                                {filtrosActivos.length > 0 && (
                                    <div className="flex flex-wrap gap-2">
                                        {filtrosActivos.map((filtro) => (
                                            <button
                                                key={filtro.id}
                                                type="button"
                                                aria-label={`Quitar filtro ${filtro.label}`}
                                                onClick={filtro.quitar}
                                                disabled={form.processing}
                                                className="inline-flex max-w-full items-center gap-1 rounded-sm border border-primary/30 bg-primary/10 px-2 py-1 text-left text-xs text-primary focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-50"
                                            >
                                                <span className="break-words">
                                                    {filtro.label}
                                                </span>
                                                <XIcon
                                                    aria-hidden="true"
                                                    className="size-3 shrink-0"
                                                />
                                            </button>
                                        ))}
                                    </div>
                                )}
                                {filtrados.length === 0 && (
                                    <p className="text-sm text-educatio-warning">
                                        {alumnos.length === 0
                                            ? 'No hay alumnos activos disponibles.'
                                            : 'Ningún alumno coincide con esta combinación de filtros. Podés quitar filtros o limpiarlos.'}
                                    </p>
                                )}
                                {cicloLectivo !== 'todos' &&
                                    origen === 'sin_grupo' && (
                                        <p className="text-sm text-educatio-warning">
                                            Los alumnos sin grupo no tienen año
                                            lectivo actual. Quitá uno de esos
                                            filtros para obtener resultados.
                                        </p>
                                    )}
                            </div>
                            <div className="mb-3 flex flex-wrap items-center gap-3">
                                <EureButtonSecondary
                                    type="button"
                                    disabled={
                                        form.processing ||
                                        !grupo.activo ||
                                        seleccionables.length === 0 ||
                                        elegidos.length +
                                            seleccionables.length >
                                            200
                                    }
                                    onClick={() => {
                                        form.clearErrors();
                                        form.setData('alumnos', [
                                            ...form.data.alumnos,
                                            ...seleccionables.map((alumno) => ({
                                                alumno_id: alumno.id,
                                                version_historial:
                                                    alumno.version_historial,
                                            })),
                                        ]);
                                    }}
                                >
                                    Seleccionar resultados (
                                    {seleccionables.length})
                                </EureButtonSecondary>
                                <EureButtonSecondary
                                    type="button"
                                    disabled={
                                        form.processing || elegidos.length === 0
                                    }
                                    onClick={() => {
                                        form.clearErrors();
                                        form.setData('alumnos', []);
                                    }}
                                >
                                    Limpiar selección
                                </EureButtonSecondary>
                                <p className="text-sm text-muted-foreground">
                                    Hasta 200 alumnos por operación. La
                                    selección se conserva al filtrar.
                                </p>
                            </div>
                        </>
                    )}
                    <EureDataTable
                        key={
                            revisando
                                ? 'revision'
                                : `${origen}:${buscar}:${cicloLectivo}:${cohorteNacimiento}`
                        }
                        variant="plain"
                        columns={revisando ? columnasRevision : columnas}
                        data={revisando ? elegidos : filtrados}
                        initialSorting={[{ id: 'id', desc: true }]}
                        initialPageSize={10}
                        showGlobalFilter={false}
                        showExportButtons={false}
                    />
                </EducatioCard>
                <EducatioCard className="sticky bottom-0 z-10">
                    <form onSubmit={guardar} className="space-y-3">
                        <p aria-live="polite" className="text-sm">
                            <strong>{elegidos.length} seleccionados</strong>:{' '}
                            {ingresos} ingresos y {traslados} traslados.
                            {!revisando &&
                                ocultos > 0 &&
                                ` ${ocultos} seleccionados quedan fuera del filtro actual.`}
                        </p>
                        <EureInputGroup
                            label="Motivo común del movimiento"
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
                        {form.hasErrors && (
                            <div
                                role="alert"
                                className="text-sm text-eure-error"
                            >
                                {Object.entries(form.errors)
                                    .filter(([campo]) => campo !== 'motivo')
                                    .map(([campo, error]) => (
                                        <p key={campo}>{error}</p>
                                    ))}
                            </div>
                        )}
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <p className="text-sm text-muted-foreground">
                                Si un alumno cambió de grupo, el lote completo
                                se cancela para revisarlo.
                            </p>
                            <div className="flex flex-wrap gap-2">
                                {form.hasErrors && (
                                    <EureButtonSecondary
                                        type="button"
                                        disabled={form.processing}
                                        onClick={() =>
                                            router.visit(create(grupo.id), {
                                                preserveState: false,
                                            })
                                        }
                                    >
                                        Recargar y limpiar selección
                                    </EureButtonSecondary>
                                )}
                                {revisando && (
                                    <EureButtonSecondary
                                        type="button"
                                        disabled={form.processing}
                                        onClick={() => setRevisando(false)}
                                    >
                                        Modificar selección
                                    </EureButtonSecondary>
                                )}
                                <EureButtonPrimary
                                    type="submit"
                                    disabled={
                                        form.processing ||
                                        !grupo.activo ||
                                        elegidos.length === 0
                                    }
                                >
                                    {form.processing
                                        ? 'Guardando…'
                                        : revisando
                                          ? `Confirmar ${elegidos.length} asignaciones`
                                          : 'Revisar asignaciones'}
                                </EureButtonPrimary>
                            </div>
                        </div>
                    </form>
                </EducatioCard>
            </div>
        </>
    );
}

AsignarAlumnos.layout = ({ grupo }: Props) => ({
    breadcrumbs: [
        { title: 'Grupos', href: index.url() },
        { title: `Grupo #${formatId(grupo.id, 4)}`, href: show.url(grupo.id) },
        { title: 'Asignar alumnos', href: create.url(grupo.id) },
    ],
});
