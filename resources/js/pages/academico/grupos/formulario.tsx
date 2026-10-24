import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, LayersIcon, PlusIcon, SaveIcon } from 'lucide-react';
import type { FormEvent } from 'react';
import EducatioCard from '@/components/educatio-card';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import {
    create,
    edit,
    index,
    show,
    store,
    update,
} from '@/routes/academico/grupos';
import { CAMPOS_GRUPO, consultaFiltros, nombreCatalogo } from './types';
import type { CatalogosGrupo, FiltrosGrupo, Grupo } from './types';

type DatosGrupo = {
    ciclo_lectivo: string;
    nivel_id: string;
    curso_id: string;
    division_id: string;
    turno_id: string;
    plan_estudio_id: string;
};

type Props = {
    grupo: Grupo | null;
    filters: FiltrosGrupo;
    catalogos: CatalogosGrupo;
    iniciales: Partial<Record<keyof DatosGrupo, number | string | null>>;
};

export default function FormularioGrupo({
    grupo,
    filters,
    catalogos,
    iniciales,
}: Props) {
    const origen = grupo ?? iniciales;
    const form = useForm<DatosGrupo>({
        ciclo_lectivo: String(origen.ciclo_lectivo ?? ''),
        nivel_id: String(origen.nivel_id ?? ''),
        curso_id: String(origen.curso_id ?? ''),
        division_id: String(origen.division_id ?? ''),
        turno_id: String(origen.turno_id ?? ''),
        plan_estudio_id: String(origen.plan_estudio_id ?? ''),
    });
    const contexto = { query: { filtros: consultaFiltros(filters) } };
    const volver = grupo
        ? show(grupo.id, contexto)
        : index({ query: consultaFiltros(filters) });
    const titulo = grupo ? 'Editar grupo' : 'Nuevo grupo';
    const nivel = catalogos.niveles.find(
        (registro) => String(registro.id) === form.data.nivel_id,
    );
    const curso = catalogos.cursos.find(
        (registro) => String(registro.id) === form.data.curso_id,
    );
    const division = catalogos.divisiones.find(
        (registro) => String(registro.id) === form.data.division_id,
    );
    const turno = catalogos.turnos.find(
        (registro) => String(registro.id) === form.data.turno_id,
    );
    const descripcion =
        nivel && curso && division && turno && form.data.ciclo_lectivo
            ? `${form.data.ciclo_lectivo} · ${nivel.descripcion} · ${curso.descripcion} ${division.descripcion} · ${turno.descripcion}`
            : 'Completá ciclo, nivel, curso, división y turno para identificar el grupo.';

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const boton = (event.nativeEvent as SubmitEvent).submitter;
        const crearOtro =
            !grupo &&
            boton instanceof HTMLButtonElement &&
            boton.value === 'otro';

        form.transform((datos) => ({
            ...datos,
            crear_otro: crearOtro,
            filtros: consultaFiltros(filters),
        }));

        const opciones = {
            preserveScroll: true,
            onSuccess: () => {
                if (crearOtro) {
                    form.setData((datos) => ({
                        ...datos,
                        curso_id: '',
                        division_id: '',
                    }));
                    form.clearErrors();
                    document.getElementById('curso_id')?.focus();
                }
            },
        };

        if (grupo) {
            form.patch(update.url(grupo.id), opciones);
        } else {
            form.post(store.url(), opciones);
        }
    };

    return (
        <>
            <Head title={titulo} />
            <div className="mx-auto flex w-full max-w-5xl flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                        <LayersIcon className="size-7 text-educatio-icon" />
                        {titulo}
                    </h1>
                    <EureButtonSecondary asChild>
                        <Link href={volver}>
                            <ArrowLeftIcon className="size-4" />
                            Volver
                        </Link>
                    </EureButtonSecondary>
                </EducatioCard>
                <EducatioCard>
                    <form onSubmit={guardar} noValidate className="space-y-5">
                        {grupo && !grupo.activo && (
                            <p className="text-sm text-educatio-warning">
                                Este grupo está inactivo. Guardar sus datos no
                                lo reactiva.
                            </p>
                        )}
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <EureInputGroup
                                name="ciclo_lectivo"
                                label="Ciclo lectivo"
                                type="number"
                                min={1900}
                                max={9999}
                                value={form.data.ciclo_lectivo}
                                onChange={(event) =>
                                    form.setData(
                                        'ciclo_lectivo',
                                        event.target.value,
                                    )
                                }
                                autoFocus={!iniciales.nivel_id}
                                required
                                error={Boolean(form.errors.ciclo_lectivo)}
                                errorMessage={form.errors.ciclo_lectivo}
                            />
                            {CAMPOS_GRUPO.map(({ campo, catalogo, label }) => {
                                const porNivel =
                                    campo === 'curso_id' ||
                                    campo === 'plan_estudio_id';
                                const opciones = catalogos[catalogo]
                                    .filter(
                                        (registro) =>
                                            (registro.activo ||
                                                registro.id ===
                                                    grupo?.[campo]) &&
                                            (!porNivel ||
                                                String(registro.nivel_id) ===
                                                    form.data.nivel_id),
                                    )
                                    .map((registro) => ({
                                        value: String(registro.id),
                                        label: nombreCatalogo(registro),
                                    }));

                                return (
                                    <EureSelectGroup
                                        key={campo}
                                        name={campo}
                                        label={label}
                                        options={opciones}
                                        value={
                                            opciones.find(
                                                (opcion) =>
                                                    opcion.value ===
                                                    form.data[campo],
                                            ) ?? null
                                        }
                                        onChange={(opcion) =>
                                            form.setData((datos) => ({
                                                ...datos,
                                                ...(campo === 'nivel_id'
                                                    ? {
                                                          curso_id: '',
                                                          plan_estudio_id: '',
                                                      }
                                                    : {}),
                                                [campo]: String(
                                                    opcion?.value ?? '',
                                                ),
                                            }))
                                        }
                                        isDisabled={
                                            porNivel && !form.data.nivel_id
                                        }
                                        isClearable={
                                            campo === 'plan_estudio_id'
                                        }
                                        required={campo !== 'plan_estudio_id'}
                                        placeholder={
                                            campo === 'plan_estudio_id'
                                                ? 'Sin plan'
                                                : 'Buscar y seleccionar'
                                        }
                                        helperText={
                                            campo === 'plan_estudio_id'
                                                ? 'Opcional. Podés completarlo más adelante.'
                                                : opciones.length === 0 &&
                                                    (!porNivel ||
                                                        form.data.nivel_id)
                                                  ? 'No hay opciones activas. Revisá el catálogo correspondiente.'
                                                  : undefined
                                        }
                                        error={Boolean(form.errors[campo])}
                                        errorMessage={form.errors[campo]}
                                        autoFocus={
                                            campo === 'curso_id' &&
                                            Boolean(iniciales.nivel_id)
                                        }
                                    />
                                );
                            })}
                        </div>
                        <div
                            className="rounded-md border bg-muted/30 p-3"
                            aria-live="polite"
                        >
                            <p className="text-sm font-medium">
                                Descripción del grupo
                            </p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {descripcion}
                            </p>
                        </div>
                        {form.errors.ciclo_lectivo?.startsWith('Ya existe') && (
                            <Link
                                className="block text-sm text-primary underline underline-offset-4"
                                href={index({
                                    query: {
                                        ciclo_lectivo: form.data.ciclo_lectivo,
                                        nivel_id: form.data.nivel_id,
                                        curso_id: form.data.curso_id,
                                        division_id: form.data.division_id,
                                        turno_id: form.data.turno_id,
                                    },
                                })}
                            >
                                Ver el grupo existente y reactivarlo si
                                corresponde
                            </Link>
                        )}
                        <div className="flex flex-wrap justify-end gap-2 border-t pt-4">
                            <EureButtonSecondary asChild>
                                <Link href={volver}>Cancelar</Link>
                            </EureButtonSecondary>
                            <EureButtonPrimary
                                type="submit"
                                value="guardar"
                                loading={form.processing}
                            >
                                <SaveIcon className="size-4" />
                                Guardar
                            </EureButtonPrimary>
                            {!grupo && (
                                <EureButtonSecondary
                                    type="submit"
                                    value="otro"
                                    disabled={form.processing}
                                >
                                    <PlusIcon className="size-4" />
                                    Guardar y crear otro
                                </EureButtonSecondary>
                            )}
                        </div>
                    </form>
                </EducatioCard>
            </div>
        </>
    );
}

FormularioGrupo.layout = ({ grupo, filters }: Props) => ({
    breadcrumbs: [
        {
            title: 'Grupos',
            href: index.url({ query: consultaFiltros(filters) }),
        },
        {
            title: grupo ? 'Editar grupo' : 'Nuevo grupo',
            href: grupo
                ? edit.url(grupo.id, {
                      query: { filtros: consultaFiltros(filters) },
                  })
                : create.url({ query: { filtros: consultaFiltros(filters) } }),
        },
    ],
});
