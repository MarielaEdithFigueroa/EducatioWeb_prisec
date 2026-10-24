import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    DoorOpenIcon,
    PencilIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { formatId } from '@/lib/utils';
import DetalleCampo, {
    BloqueDetalle,
} from '@/pages/academico/alumnos/partials/detalle-campo';
import { edit, index, show } from '@/routes/academico/aulas';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';
import FotosAula from './fotos';
import type { Aula } from './types';
import { useEstadoAula } from './use-estado-aula';

type Props = { aula: Aula; fotos: Adjunto[]; configFotos: ConfigAdjuntos };

export default function VerAula({ aula, fotos, configFotos }: Props) {
    const { procesando, cambiarEstado } = useEstadoAula();

    return (
        <>
            <Head title={aula.descripcion} />
            <div className="mx-auto flex w-full max-w-4xl flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <DoorOpenIcon className="size-7 shrink-0 text-educatio-icon" />
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {aula.descripcion}
                            </h1>
                            <Badge variant="outline">
                                {aula.activo ? 'Activa' : 'Inactiva'}
                            </Badge>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Aula #{formatId(aula.id, 3)}
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <EureButtonSecondary asChild>
                            <Link href={index()}>
                                <ArrowLeftIcon className="size-4" />
                                Volver
                            </Link>
                        </EureButtonSecondary>
                        <EureButtonPrimary
                            onClick={() => router.visit(edit(aula.id))}
                        >
                            <PencilIcon className="size-4" />
                            Editar
                        </EureButtonPrimary>
                    </div>
                </EducatioCard>
                <BloqueDetalle title="Datos del aula">
                    <DetalleCampo
                        label="Descripción corta"
                        value={aula.descripcion}
                    />
                    <DetalleCampo
                        label="Capacidad"
                        value={
                            aula.capacidad == null
                                ? 'Sin informar'
                                : `${aula.capacidad} personas`
                        }
                    />
                    <DetalleCampo
                        label="Aula especial"
                        value={aula.es_especial ? 'Sí' : 'No'}
                    />
                    {aula.es_especial && (
                        <DetalleCampo
                            label="Especialidad"
                            value={aula.especialidad}
                        />
                    )}
                    <DetalleCampo
                        label="Descripción larga"
                        value={
                            <span className="whitespace-pre-line">
                                {aula.descripcion_larga || '—'}
                            </span>
                        }
                        className="md:col-span-2 xl:col-span-3"
                    />
                </BloqueDetalle>
                <FotosAula
                    aulaId={aula.id}
                    fotos={fotos}
                    configuracion={configFotos}
                />
                <div className="flex justify-end">
                    <EureButtonSecondary
                        disabled={procesando !== null}
                        onClick={() => void cambiarEstado(aula)}
                    >
                        {aula.activo ? (
                            <Trash2Icon className="size-4" />
                        ) : (
                            <RotateCcwIcon className="size-4" />
                        )}
                        {aula.activo ? 'Desactivar' : 'Reactivar'}
                    </EureButtonSecondary>
                </div>
            </div>
        </>
    );
}

VerAula.layout = ({ aula }: Props) => ({
    breadcrumbs: [
        { title: 'Aulas', href: index.url() },
        { title: aula.descripcion, href: show.url(aula.id) },
    ],
});
