import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    PencilIcon,
    UserCogIcon,
    IdCardIcon,
    MapPinIcon,
    BriefcaseBusinessIcon,
    NotebookPenIcon,
    UsersIcon,
} from 'lucide-react';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { formatCuilt } from '@/lib/cuit';
import { formatDate } from '@/lib/date';
import DetalleCampo, {
    BloqueDetalle,
} from '@/pages/academico/alumnos/partials/detalle-campo';
import { show as alumnoShow } from '@/routes/academico/alumnos';
import { edit, index, show } from '@/routes/academico/responsables';
import type { ResponsableResumen } from './types';

type Localidad = {
    nombre: string;
    activo: boolean;
    provincia: { nombre: string };
};

type ResponsableDetalle = ResponsableResumen & {
    fecha_nacimiento: string | null;
    sexo_registral: string | null;
    genero: string | null;
    genero_autodescripcion: string | null;
    domicilio: string | null;
    ciudad: Localidad | null;
    cpa: string | null;
    profesion: string | null;
    domicilio_laboral: string | null;
    ciudad_laboral: Localidad | null;
    cpa_laboral: string | null;
    telefono_laboral_e164: string | null;
    observaciones: string | null;
};

type Props = { responsable: ResponsableDetalle };

export default function VerResponsable({ responsable }: Props) {
    return (
        <>
            <Head title={`${responsable.apellido}, ${responsable.nombre}`} />
            <div className="mx-auto flex w-full max-w-6xl flex-col gap-4">
                <EducatioCard className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <UserCogIcon
                                className="size-7 shrink-0 text-educatio-icon"
                                aria-hidden="true"
                            />
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {responsable.apellido}, {responsable.nombre}
                            </h1>
                            <Badge
                                variant="outline"
                                className={
                                    responsable.activo
                                        ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                        : 'border-eure-error/30 bg-eure-error/10 text-eure-error'
                                }
                            >
                                {responsable.activo ? 'Activo' : 'Inactivo'}
                            </Badge>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Responsable #{responsable.id}
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
                            onClick={() => router.visit(edit(responsable.id))}
                        >
                            <PencilIcon className="size-4" />
                            Editar
                        </EureButtonPrimary>
                    </div>
                </EducatioCard>

                <div className="space-y-4">
                    <BloqueDetalle title="Datos personales" icon={IdCardIcon}>
                        <DetalleCampo
                            label="Apellido"
                            value={responsable.apellido}
                        />
                        <DetalleCampo
                            label="Nombre"
                            value={responsable.nombre}
                        />
                        <DetalleCampo
                            label="Nombre elegido"
                            value={responsable.nombre_elegido}
                        />
                        <DetalleCampo
                            label="Tipo de documento"
                            value={responsable.tipo_documento?.nombre}
                        />
                        <DetalleCampo
                            label="Número de documento"
                            value={responsable.numero_documento}
                        />
                        <DetalleCampo
                            label="CUIL/T"
                            value={formatCuilt(responsable.cuilt)}
                        />
                        <DetalleCampo
                            label="Fecha de nacimiento"
                            value={formatDate(
                                responsable.fecha_nacimiento,
                                '—',
                            )}
                        />
                        <DetalleCampo
                            label="Sexo registral"
                            value={responsable.sexo_registral}
                        />
                        <DetalleCampo
                            label="Género"
                            value={responsable.genero}
                        />
                        {responsable.genero_autodescripcion && (
                            <DetalleCampo
                                label="Autodescripción de género"
                                value={responsable.genero_autodescripcion}
                            />
                        )}
                    </BloqueDetalle>
                    <BloqueDetalle
                        title="Contacto y domicilio"
                        icon={MapPinIcon}
                    >
                        <DetalleCampo label="Email" value={responsable.email} />
                        <DetalleCampo
                            label="Teléfono"
                            value={responsable.telefono_e164}
                        />
                        <DetalleCampo
                            label="Domicilio"
                            value={responsable.domicilio}
                        />
                        <DetalleCampo
                            label="Localidad"
                            value={
                                responsable.ciudad &&
                                `${responsable.ciudad.nombre}${responsable.ciudad.activo ? '' : ' (inactiva)'}`
                            }
                        />
                        <DetalleCampo
                            label="Provincia"
                            value={responsable.ciudad?.provincia.nombre}
                        />
                        <DetalleCampo label="CPA" value={responsable.cpa} />
                    </BloqueDetalle>
                    <BloqueDetalle
                        title="Datos laborales"
                        icon={BriefcaseBusinessIcon}
                    >
                        <DetalleCampo
                            label="Profesión u ocupación"
                            value={responsable.profesion}
                        />
                        <DetalleCampo
                            label="Domicilio laboral"
                            value={responsable.domicilio_laboral}
                        />
                        <DetalleCampo
                            label="Localidad laboral"
                            value={
                                responsable.ciudad_laboral &&
                                `${responsable.ciudad_laboral.nombre}${responsable.ciudad_laboral.activo ? '' : ' (inactiva)'}`
                            }
                        />
                        <DetalleCampo
                            label="Provincia"
                            value={responsable.ciudad_laboral?.provincia.nombre}
                        />
                        <DetalleCampo
                            label="CPA laboral"
                            value={responsable.cpa_laboral}
                        />
                        <DetalleCampo
                            label="Teléfono laboral"
                            value={responsable.telefono_laboral_e164}
                        />
                    </BloqueDetalle>
                    <BloqueDetalle title="Observaciones" icon={NotebookPenIcon}>
                        <DetalleCampo
                            label="Observaciones de la persona"
                            value={
                                responsable.observaciones && (
                                    <span className="whitespace-pre-wrap">
                                        {responsable.observaciones}
                                    </span>
                                )
                            }
                            className="md:col-span-2 xl:col-span-3"
                        />
                    </BloqueDetalle>
                    <BloqueDetalle
                        title="Alumnos vinculados"
                        icon={UsersIcon}
                        description="Accedé al alumno para consultar o editar el vínculo."
                    >
                        {responsable.alumnos_responsables.map((vinculo) => (
                            <DetalleCampo
                                key={vinculo.id}
                                label={`Legajo ${vinculo.alumno.legajo}`}
                                value={
                                    <>
                                        <Link
                                            href={alumnoShow(
                                                vinculo.alumno.id,
                                                {
                                                    query: {
                                                        seccion: 'responsables',
                                                    },
                                                },
                                            )}
                                            className="rounded-sm text-primary underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                        >
                                            {vinculo.alumno.apellido},{' '}
                                            {vinculo.alumno.nombre}
                                        </Link>
                                        {vinculo.tipo_vinculo && (
                                            <span className="block text-xs text-muted-foreground">
                                                {vinculo.tipo_vinculo.nombre}
                                            </span>
                                        )}
                                        {!vinculo.activo && (
                                            <span className="block text-xs text-muted-foreground">
                                                Vínculo inactivo
                                            </span>
                                        )}
                                        {!vinculo.alumno.activo && (
                                            <span className="block text-xs text-muted-foreground">
                                                Alumno inactivo
                                            </span>
                                        )}
                                    </>
                                }
                            />
                        ))}
                        {responsable.alumnos_responsables.length === 0 && (
                            <DetalleCampo
                                label="Vínculos"
                                value="Sin alumnos vinculados"
                            />
                        )}
                    </BloqueDetalle>
                </div>
            </div>
        </>
    );
}

VerResponsable.layout = ({ responsable }: Props) => ({
    breadcrumbs: [
        { title: 'Responsables', href: index.url() },
        {
            title: `${responsable.apellido}, ${responsable.nombre}`,
            href: show.url(responsable.id),
        },
    ],
});
