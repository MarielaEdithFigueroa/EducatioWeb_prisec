import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, DoorOpenIcon, SaveIcon } from 'lucide-react';
import type { FormEvent } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Checkbox } from '@/components/ui/checkbox';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import {
    create,
    edit,
    index,
    show,
    store,
    update,
} from '@/routes/academico/aulas';
import type { Aula } from './types';

type Props = { aula: Aula | null };

export default function FormularioAula({ aula }: Props) {
    const form = useForm({
        descripcion: aula?.descripcion ?? '',
        descripcion_larga: aula?.descripcion_larga ?? '',
        capacidad: aula?.capacidad == null ? '' : String(aula.capacidad),
        es_especial: aula?.es_especial ?? false,
        especialidad: aula?.especialidad ?? '',
    });
    const titulo = aula ? 'Editar aula' : 'Nueva aula';
    const volver = aula ? show(aula.id) : index();

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.submit(aula ? update(aula.id) : store());
    };

    return (
        <>
            <Head title={titulo} />
            <div className="mx-auto flex w-full max-w-4xl flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                        <DoorOpenIcon className="size-7 text-educatio-icon" />
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
                        {aula && !aula.activo && (
                            <p className="text-sm text-educatio-warning">
                                Esta aula está inactiva. Guardar sus datos no la
                                reactiva.
                            </p>
                        )}
                        <div className="grid gap-4 sm:grid-cols-[minmax(0,1fr)_10rem]">
                            <EureInputGroup
                                name="descripcion"
                                label="Descripción corta"
                                value={form.data.descripcion}
                                onChange={(event) =>
                                    form.setData(
                                        'descripcion',
                                        event.target.value,
                                    )
                                }
                                maxLength={100}
                                placeholder="Ej.: Aula 2"
                                required
                                autoFocus
                                error={Boolean(form.errors.descripcion)}
                                errorMessage={form.errors.descripcion}
                            />
                            <EureInputGroup
                                name="capacidad"
                                label="Capacidad"
                                type="number"
                                min={1}
                                max={65535}
                                step={1}
                                value={form.data.capacidad}
                                onChange={(event) =>
                                    form.setData(
                                        'capacidad',
                                        event.target.value,
                                    )
                                }
                                helperText="Personas (opcional)"
                                error={Boolean(form.errors.capacidad)}
                                errorMessage={form.errors.capacidad}
                            />
                        </div>
                        <EureTextareaGroup
                            name="descripcion_larga"
                            label="Descripción larga"
                            value={form.data.descripcion_larga}
                            onChange={(event) =>
                                form.setData(
                                    'descripcion_larga',
                                    event.target.value,
                                )
                            }
                            maxLength={2000}
                            rows={3}
                            placeholder="Ej.: Edificio viejo, primer piso"
                            helperText="Ubicación u otros detalles (opcional)."
                            error={Boolean(form.errors.descripcion_larga)}
                            errorMessage={form.errors.descripcion_larga}
                        />
                        <div>
                            <label
                                htmlFor="es_especial"
                                className="flex w-fit cursor-pointer items-center gap-2 text-sm font-semibold"
                            >
                                <Checkbox
                                    id="es_especial"
                                    checked={form.data.es_especial}
                                    onCheckedChange={(checked) => {
                                        form.setData((datos) => ({
                                            ...datos,
                                            es_especial: checked === true,
                                            especialidad:
                                                checked === true
                                                    ? datos.especialidad
                                                    : '',
                                        }));
                                        form.clearErrors(
                                            'especialidad',
                                            'es_especial',
                                        );
                                    }}
                                />
                                Aula especial
                            </label>
                            {form.errors.es_especial && (
                                <p className="mt-1 text-xs text-eure-error">
                                    {form.errors.es_especial}
                                </p>
                            )}
                        </div>
                        {form.data.es_especial && (
                            <EureInputGroup
                                name="especialidad"
                                label="Especialidad"
                                value={form.data.especialidad}
                                onChange={(event) =>
                                    form.setData(
                                        'especialidad',
                                        event.target.value,
                                    )
                                }
                                maxLength={100}
                                placeholder="Ej.: Música"
                                helperText="Texto libre: música, informática, laboratorio…"
                                required
                                error={Boolean(form.errors.especialidad)}
                                errorMessage={form.errors.especialidad}
                            />
                        )}
                        <div className="flex flex-wrap justify-end gap-2 border-t pt-4">
                            <EureButtonSecondary asChild>
                                <Link href={volver}>Cancelar</Link>
                            </EureButtonSecondary>
                            <EureButtonPrimary
                                type="submit"
                                loading={form.processing}
                            >
                                <SaveIcon className="size-4" />
                                Guardar
                            </EureButtonPrimary>
                        </div>
                    </form>
                </EducatioCard>
            </div>
        </>
    );
}

FormularioAula.layout = ({ aula }: Props) => ({
    breadcrumbs: [
        { title: 'Aulas', href: index.url() },
        {
            title: aula ? 'Editar aula' : 'Nueva aula',
            href: aula ? edit.url(aula.id) : create.url(),
        },
    ],
});
