import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import AnioLectivoController from '@/actions/App/Http/Controllers/Academico/AnioLectivoController';
import Heading from '@/components/heading';
import {
    EureButtonPrimary,
    EureButtonSecondary,
    EureInputGroup,
    EureSwitch,
} from '@/components/ui/eure';
import { index } from '@/routes/academico/anios-lectivos';

type AnioLectivo = {
    id: number;
    activo: boolean;
    anio: number;
    vigente: boolean;
};

export default function AnioLectivoForm({
    anioLectivo,
}: {
    anioLectivo?: AnioLectivo;
}) {
    const editando = Boolean(anioLectivo);

    const form = useForm({
        activo: anioLectivo?.activo ?? true,
        anio: anioLectivo?.anio ?? new Date().getFullYear(),
        vigente: anioLectivo?.vigente ?? false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (anioLectivo) {
            form.put(AnioLectivoController.update(anioLectivo.id).url);
        } else {
            form.post(AnioLectivoController.store().url);
        }
    };

    const titulo = editando ? 'Editar año lectivo' : 'Nuevo año lectivo';

    return (
        <>
            <Head title={titulo} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading title={titulo} description="Datos del ciclo lectivo" />

                <form onSubmit={submit} className="max-w-2xl space-y-6">
                    <EureInputGroup
                        label="Año"
                        name="anio"
                        type="number"
                        min={1990}
                        max={2100}
                        value={String(form.data.anio)}
                        onChange={(e) =>
                            form.setData('anio', Number(e.target.value))
                        }
                        error={Boolean(form.errors.anio)}
                        errorMessage={form.errors.anio}
                        classNameWrapper="w-32"
                        required
                        autoFocus
                    />

                    <EureSwitch
                        id="vigente"
                        label="Vigente"
                        checked={form.data.vigente}
                        onCheckedChange={(checked) =>
                            form.setData('vigente', checked)
                        }
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
                            {editando ? 'Guardar cambios' : 'Crear año lectivo'}
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

AnioLectivoForm.layout = {
    breadcrumbs: [
        { title: 'Años lectivos', href: index() },
        { title: 'Formulario', href: index() },
    ],
};
