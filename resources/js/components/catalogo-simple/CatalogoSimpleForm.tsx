import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import Heading from '@/components/heading';
import {
    EureButtonPrimary,
    EureButtonSecondary,
    EureInputGroup,
    EureSwitch,
} from '@/components/ui/eure';

import type { CatalogoSimple, CatalogoSimpleController } from './types';

interface Props {
    entidad: string;
    controller: CatalogoSimpleController;
    item?: CatalogoSimple;
}

export default function CatalogoSimpleForm({
    entidad,
    controller,
    item,
}: Props) {
    const editando = Boolean(item);

    const form = useForm({
        activo: item?.activo ?? true,
        codigo: item?.codigo ?? '',
        descripcion: item?.descripcion ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (item) {
            form.put(controller.update(item.id).url);
        } else {
            form.post(controller.store().url);
        }
    };

    const titulo = editando ? `Editar ${entidad}` : `Nuevo ${entidad}`;

    return (
        <>
            <Head title={titulo} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading title={titulo} description={`Datos del ${entidad}`} />

                <form onSubmit={submit} className="max-w-2xl space-y-6">
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
                            autoFocus
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
                            {editando ? 'Guardar cambios' : `Crear ${entidad}`}
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
