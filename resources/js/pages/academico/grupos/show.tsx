import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    LayersIcon,
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
import { edit, index, show } from '@/routes/academico/grupos';
import { consultaFiltros, nombreCatalogo } from './types';
import type { FiltrosGrupo, Grupo } from './types';
import { useEstadoGrupo } from './use-estado-grupo';

type Props = { grupo: Grupo; filters: FiltrosGrupo };

export default function VerGrupo({ grupo, filters }: Props) {
    const { procesando, cambiarEstado } = useEstadoGrupo();

    return (
        <>
            <Head title={grupo.descripcion} />
            <div className="mx-auto flex w-full max-w-5xl flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                        <div className="flex items-center gap-2">
                            <LayersIcon className="size-7 shrink-0 text-educatio-icon" />
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Grupo #{formatId(grupo.id, 4)}
                            </h1>
                            <Badge variant="outline">
                                {grupo.activo ? 'Activo' : 'Inactivo'}
                            </Badge>
                        </div>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {grupo.descripcion}
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <EureButtonSecondary asChild>
                            <Link
                                href={index({
                                    query: consultaFiltros(filters),
                                })}
                            >
                                <ArrowLeftIcon className="size-4" />
                                Volver
                            </Link>
                        </EureButtonSecondary>
                        <EureButtonPrimary
                            onClick={() =>
                                router.visit(
                                    edit(grupo.id, {
                                        query: {
                                            filtros: consultaFiltros(filters),
                                        },
                                    }),
                                )
                            }
                        >
                            <PencilIcon className="size-4" />
                            Editar
                        </EureButtonPrimary>
                    </div>
                </EducatioCard>
                <BloqueDetalle title="Datos del grupo">
                    <DetalleCampo
                        label="Ciclo lectivo"
                        value={grupo.ciclo_lectivo}
                    />
                    <DetalleCampo
                        label="Nivel"
                        value={nombreCatalogo(grupo.nivel)}
                    />
                    <DetalleCampo
                        label="Curso"
                        value={nombreCatalogo(grupo.curso)}
                    />
                    <DetalleCampo
                        label="División"
                        value={nombreCatalogo(grupo.division)}
                    />
                    <DetalleCampo
                        label="Turno"
                        value={nombreCatalogo(grupo.turno)}
                    />
                    <DetalleCampo
                        label="Plan de estudio"
                        value={nombreCatalogo(grupo.plan_estudio)}
                    />
                </BloqueDetalle>
                <div className="flex justify-end">
                    <EureButtonSecondary
                        disabled={procesando !== null}
                        onClick={() => void cambiarEstado(grupo)}
                    >
                        {grupo.activo ? (
                            <Trash2Icon className="size-4" />
                        ) : (
                            <RotateCcwIcon className="size-4" />
                        )}
                        {grupo.activo ? 'Desactivar' : 'Reactivar'}
                    </EureButtonSecondary>
                </div>
            </div>
        </>
    );
}

VerGrupo.layout = ({ grupo, filters }: Props) => ({
    breadcrumbs: [
        {
            title: 'Grupos',
            href: index.url({ query: consultaFiltros(filters) }),
        },
        {
            title: `Grupo #${formatId(grupo.id, 4)}`,
            href: show.url(grupo.id, {
                query: { filtros: consultaFiltros(filters) },
            }),
        },
    ],
});
