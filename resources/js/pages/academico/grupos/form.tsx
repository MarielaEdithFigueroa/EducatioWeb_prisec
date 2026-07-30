import { Head, useForm } from '@inertiajs/react';
import { useMemo } from 'react';
import type { FormEvent } from 'react';

import GrupoController from '@/actions/App/Http/Controllers/Academico/GrupoController';
import Heading from '@/components/heading';
import {
    EureButtonPrimary,
    EureButtonSecondary,
    EureSelectGroup,
    EureSwitch,
} from '@/components/ui/eure';
import type { EureSelectOption } from '@/components/ui/eure';
import { catalogoOption } from '@/lib/options';
import { index } from '@/routes/academico/grupos';

type AnioLectivo = {
    id: number;
    anio: number;
    vigente: boolean;
    activo: boolean;
};
type Curso = {
    id: number;
    codigo: string;
    descripcion: string;
    activo: boolean;
    nivel: { id: number; codigo: string; descripcion: string } | null;
};
type Catalogo = {
    id: number;
    codigo: string;
    descripcion: string;
    activo: boolean;
};

type Grupo = {
    id: number;
    activo: boolean;
    anio_lectivo_id: number;
    curso_id: number;
    division_id: number;
    turno_id: number;
};

interface Props {
    grupo?: Grupo;
    aniosLectivos: AnioLectivo[];
    cursos: Curso[];
    divisiones: Catalogo[];
    turnos: Catalogo[];
}

const sufijoInactivo = (label: string, activo: boolean): string =>
    activo ? label : `${label} (inactivo)`;

export default function GrupoForm({
    grupo,
    aniosLectivos,
    cursos,
    divisiones,
    turnos,
}: Props) {
    const editando = Boolean(grupo);

    const form = useForm({
        activo: grupo?.activo ?? true,
        anio_lectivo_id: grupo?.anio_lectivo_id ?? (null as number | null),
        curso_id: grupo?.curso_id ?? (null as number | null),
        division_id: grupo?.division_id ?? (null as number | null),
        turno_id: grupo?.turno_id ?? (null as number | null),
    });

    const anioOptions = useMemo<EureSelectOption[]>(
        () =>
            aniosLectivos.map((anio) => ({
                value: anio.id,
                label: sufijoInactivo(
                    anio.vigente ? `${anio.anio} (vigente)` : String(anio.anio),
                    anio.activo,
                ),
            })),
        [aniosLectivos],
    );

    const cursoOptions = useMemo<EureSelectOption[]>(
        () =>
            cursos.map((curso) => ({
                value: curso.id,
                label: sufijoInactivo(
                    `${curso.nivel?.descripcion ?? '—'} · ${curso.descripcion}`,
                    curso.activo,
                ),
            })),
        [cursos],
    );

    const divisionOptions = useMemo(
        () => divisiones.map(catalogoOption),
        [divisiones],
    );
    const turnoOptions = useMemo(() => turnos.map(catalogoOption), [turnos]);

    const findValue = (
        options: EureSelectOption[],
        id: number | null,
    ): EureSelectOption | null => options.find((o) => o.value === id) ?? null;

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (grupo) {
            form.put(GrupoController.update(grupo.id).url);
        } else {
            form.post(GrupoController.store().url);
        }
    };

    const titulo = editando ? 'Editar grupo' : 'Nuevo grupo';

    return (
        <>
            <Head title={titulo} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title={titulo}
                    description="El nivel se deriva del curso elegido"
                />

                <form onSubmit={submit} className="max-w-2xl space-y-6">
                    <EureSelectGroup
                        label="Año lectivo"
                        name="anio_lectivo_id"
                        value={findValue(
                            anioOptions,
                            form.data.anio_lectivo_id,
                        )}
                        onChange={(o) =>
                            form.setData(
                                'anio_lectivo_id',
                                o ? Number(o.value) : null,
                            )
                        }
                        options={anioOptions}
                        placeholder="Seleccioná un año lectivo"
                        error={Boolean(form.errors.anio_lectivo_id)}
                        errorMessage={form.errors.anio_lectivo_id}
                        required
                    />

                    <EureSelectGroup
                        label="Curso"
                        name="curso_id"
                        value={findValue(cursoOptions, form.data.curso_id)}
                        onChange={(o) =>
                            form.setData('curso_id', o ? Number(o.value) : null)
                        }
                        options={cursoOptions}
                        placeholder="Seleccioná un curso"
                        error={Boolean(form.errors.curso_id)}
                        errorMessage={form.errors.curso_id}
                        required
                    />

                    <div className="grid gap-6 sm:grid-cols-2">
                        <EureSelectGroup
                            label="División"
                            name="division_id"
                            value={findValue(
                                divisionOptions,
                                form.data.division_id,
                            )}
                            onChange={(o) =>
                                form.setData(
                                    'division_id',
                                    o ? Number(o.value) : null,
                                )
                            }
                            options={divisionOptions}
                            placeholder="Seleccioná una división"
                            error={Boolean(form.errors.division_id)}
                            errorMessage={form.errors.division_id}
                            required
                        />

                        <EureSelectGroup
                            label="Turno"
                            name="turno_id"
                            value={findValue(turnoOptions, form.data.turno_id)}
                            onChange={(o) =>
                                form.setData(
                                    'turno_id',
                                    o ? Number(o.value) : null,
                                )
                            }
                            options={turnoOptions}
                            placeholder="Seleccioná un turno"
                            error={Boolean(form.errors.turno_id)}
                            errorMessage={form.errors.turno_id}
                            required
                        />
                    </div>

                    <EureSwitch
                        id="activo"
                        label="Activo"
                        checked={form.data.activo}
                        onCheckedChange={(checked) =>
                            form.setData('activo', checked)
                        }
                    />

                    <div className="flex items-center gap-3">
                        <EureButtonPrimary
                            type="submit"
                            loading={form.processing}
                        >
                            {editando ? 'Guardar cambios' : 'Crear grupo'}
                        </EureButtonPrimary>
                        <EureButtonSecondary
                            type="button"
                            onClick={() => window.history.back()}
                        >
                            Cancelar
                        </EureButtonSecondary>
                    </div>
                </form>
            </div>
        </>
    );
}

GrupoForm.layout = {
    breadcrumbs: [
        { title: 'Grupos', href: index() },
        { title: 'Formulario', href: index() },
    ],
};
