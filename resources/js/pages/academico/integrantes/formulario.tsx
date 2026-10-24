import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { edit, index, show } from '@/routes/academico/integrantes';
import IntegranteForm from './partials/integrante-form';
import type { IntegranteFormProps } from './partials/integrante-form';
import { contextoIntegrante } from './types';

export default function FormularioIntegrante(props: IntegranteFormProps) {
    const { integrante, filters, seccion } = props;
    const titulo = integrante ? 'Editar integrante' : 'Nuevo integrante';
    const volver = integrante
        ? show(integrante.id, contextoIntegrante(filters, seccion))
        : index({ query: filters });

    return (
        <>
            <Head title={titulo} />
            <div className="mx-auto flex w-full max-w-6xl flex-col gap-3">
                <div className="flex flex-wrap items-center gap-x-3 gap-y-2">
                    <h1 className="text-lg font-semibold tracking-tight">
                        {titulo}
                    </h1>
                    {integrante && (
                        <>
                            <span aria-hidden="true" className="text-border">
                                |
                            </span>
                            <p className="text-sm text-muted-foreground tabular-nums">
                                {integrante.apellido}, {integrante.nombre} ·
                                Legajo {integrante.legajo}
                            </p>
                        </>
                    )}
                    <span className="flex-1" />
                    <EureButtonSecondary asChild>
                        <Link href={volver}>
                            <ArrowLeftIcon aria-hidden="true" />
                            {integrante ? 'Volver a la ficha' : 'Volver'}
                        </Link>
                    </EureButtonSecondary>
                </div>

                <IntegranteForm {...props} />
            </div>
        </>
    );
}
FormularioIntegrante.layout = ({
    integrante,
    filters,
}: IntegranteFormProps) => ({
    breadcrumbs: [
        { title: 'Equipo institucional', href: index.url({ query: filters }) },
        {
            title: integrante ? 'Editar integrante' : 'Nuevo integrante',
            href: integrante
                ? edit.url(integrante.id, contextoIntegrante(filters))
                : undefined,
        },
    ],
});
