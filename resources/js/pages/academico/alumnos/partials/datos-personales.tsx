import { IdCardIcon, CalendarDaysIcon, MapPinIcon } from 'lucide-react';
import { formatCuilt } from '@/lib/cuit';
import { formatDate } from '@/lib/date';
import DetalleCampo, { BloqueDetalle } from './detalle-campo';
import type { AlumnoDetalle } from './types';

const SEXO_REGISTRAL_LABELS: Record<string, string> = {
    F: 'Femenino',
    M: 'Masculino',
    X: 'X',
};

function ubicacion(ciudad: string | null, provincia: string | null): string {
    return [ciudad, provincia].filter(Boolean).join(', ') || '—';
}

export default function DatosPersonales({ alumno }: { alumno: AlumnoDetalle }) {
    return (
        <div className="space-y-4">
            <BloqueDetalle title="Identificación" icon={IdCardIcon}>
                <DetalleCampo label="Apellido" value={alumno.apellido} />
                <DetalleCampo label="Nombre" value={alumno.nombre} />
                <DetalleCampo
                    label="Nombre elegido"
                    value={alumno.nombre_elegido}
                />
                <DetalleCampo
                    label="Tipo de documento"
                    value={alumno.tipo_documento}
                />
                <DetalleCampo
                    label="Número de documento"
                    value={alumno.numero_documento}
                />
                <DetalleCampo
                    label="CUIL/T"
                    value={formatCuilt(alumno.cuilt)}
                />
            </BloqueDetalle>

            <BloqueDetalle
                title="Nacimiento e identidad"
                icon={CalendarDaysIcon}
            >
                <DetalleCampo
                    label="Fecha de nacimiento"
                    value={formatDate(alumno.fecha_nacimiento, '—')}
                />
                <DetalleCampo
                    label="Lugar de nacimiento"
                    value={ubicacion(
                        alumno.ciudad_nacimiento,
                        alumno.provincia_nacimiento,
                    )}
                />
                <DetalleCampo
                    label="Nacionalidad"
                    value={alumno.nacionalidad}
                />
                <DetalleCampo
                    label="Sexo registral"
                    value={
                        alumno.sexo_registral
                            ? (SEXO_REGISTRAL_LABELS[alumno.sexo_registral] ??
                              alumno.sexo_registral)
                            : null
                    }
                />
                <DetalleCampo label="Género" value={alumno.genero} />
                {(alumno.genero === 'Otra identidad' ||
                    alumno.genero_autodescripcion) && (
                    <DetalleCampo
                        label="Autodescripción"
                        value={alumno.genero_autodescripcion}
                    />
                )}
                <DetalleCampo
                    label="Grupo sanguíneo"
                    value={alumno.grupo_sanguineo}
                />
            </BloqueDetalle>

            <BloqueDetalle title="Contacto y domicilio" icon={MapPinIcon}>
                <DetalleCampo label="Email" value={alumno.email} />
                <DetalleCampo label="Domicilio" value={alumno.domicilio} />
                <DetalleCampo
                    label="Localidad"
                    value={ubicacion(alumno.ciudad, alumno.provincia)}
                />
                <DetalleCampo label="CPA" value={alumno.cpa} />
            </BloqueDetalle>
        </div>
    );
}
