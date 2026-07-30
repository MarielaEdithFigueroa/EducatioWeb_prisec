import { Head, useForm } from '@inertiajs/react';
import { useMemo } from 'react';
import type { FormEvent } from 'react';

import CursoController from '@/actions/App/Http/Controllers/Academico/CursoController';
import Heading from '@/components/heading';
import {
    EureButtonPrimary,
    EureButtonSecondary,
    EureInputGroup,
    EureSelectGroup,
    EureSwitch,
} from '@/components/ui/eure';
import { catalogoOption } from '@/lib/options';
import { index } from '@/routes/academico/cursos';

type Nivel = {
    id: number;
    codigo: string;
    descripcion: string;
    activo: boolean;
};

type Curso = {
    id: number;
    activo: boolean;
    nivel_id: number;
    codigo: string;
    descripcion: string;
    orden: number;
};

export default function CursoForm({
    curso,
    niveles,
}: {
    curso?: Curso;
    niveles: Nivel[];
}) {
    const editando = Boolean(curso);

    const form = useForm({
        activo: curso?.activo ?? true,
        nivel_id: curso?.nivel_id ?? (null as number | null),
        codigo: curso?.codigo ?? '',
        descripcion: curso?.descripcion ?? '',
        orden: curso?.orden ?? 1,
    });

    const nivelOptions = useMemo(() => niveles.map(catalogoOption), [niveles]);
    const nivelValue =
        nivelOptions.find((o) => o.value === form.data.nivel_id) ?? null;

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (curso) {
            form.put(CursoController.update(curso.id).url);
        } else {
            form.post(CursoController.store().url);
        }
    };

    const titulo = editando ? 'Editar curso' : 'Nuevo curso';

    return (
        <>
            <Head title={titulo} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title={titulo}
                    description="Datos del curso (grado / año)"
                />

                <form onSubmit={submit} className="max-w-2xl space-y-6">
                    <EureSelectGroup
                        label="Nivel"
                        name="nivel_id"
                        value={nivelValue}
                        onChange={(option) =>
                            form.setData(
                                'nivel_id',
                                option ? Number(option.value) : null,
                            )
                        }
                        options={nivelOptions}
                        placeholder="Seleccioná un nivel"
                        error={Boolean(form.errors.nivel_id)}
                        errorMessage={form.errors.nivel_id}
                        required
                    />

                    <div className="grid gap-6 sm:grid-cols-[10rem_1fr]">
                        <EureInputGroup
                            label="Código"
                            name="codigo"
                            value={form.data.codigo}
                            onChange={(e) =>
                                form.setData('codigo', e.target.value)
                            }
                            error={Boolean(form.errors.codigo)}
                            errorMessage={form.errors.codigo}
                            maxLength={16}
                            required
                        />
                        <EureInputGroup
                            label="Descripción"
                            name="descripcion"
                            value={form.data.descripcion}
                            onChange={(e) =>
                                form.setData('descripcion', e.target.value)
                            }
                            error={Boolean(form.errors.descripcion)}
                            errorMessage={form.errors.descripcion}
                            maxLength={128}
                            required
                        />
                    </div>

                    <EureInputGroup
                        label="Orden"
                        name="orden"
                        type="number"
                        min={0}
                        value={String(form.data.orden)}
                        onChange={(e) =>
                            form.setData('orden', Number(e.target.value))
                        }
                        error={Boolean(form.errors.orden)}
                        errorMessage={form.errors.orden}
                        classNameWrapper="w-32"
                        helperText="Orden de aparición dentro del nivel"
                        required
                    />

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
                            {editando ? 'Guardar cambios' : 'Crear curso'}
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

CursoForm.layout = {
    breadcrumbs: [
        { title: 'Cursos', href: index() },
        { title: 'Formulario', href: index() },
    ],
};
