import { SchoolIcon, UserRoundXIcon, NotebookPenIcon } from 'lucide-react';
import { formatDate } from '@/lib/date';
import DetalleCampo, { BloqueDetalle } from './detalle-campo';
import type { AlumnoDetalle } from './types';

function valorTriestado(value: boolean | null): string {
    if (value === null) {
        return 'Sin informar';
    }

    return value ? 'Sí' : 'No';
}

export default function DatosInstitucionales({
    alumno,
}: {
    alumno: AlumnoDetalle;
}) {
    return (
        <div className="space-y-4">
            <BloqueDetalle title="Datos institucionales" icon={SchoolIcon}>
                <DetalleCampo label="Legajo" value={alumno.legajo} />
                <DetalleCampo
                    label="Fecha de ingreso"
                    value={formatDate(alumno.fecha_ingreso, '—')}
                />
                <DetalleCampo
                    label="Inicio de cursado"
                    value={formatDate(alumno.fecha_inicio_cursado, '—')}
                />
                <DetalleCampo label="Libro" value={alumno.libro} />
                <DetalleCampo label="Folio" value={alumno.folio} />
                <DetalleCampo
                    label="Autoriza el uso de imagen"
                    value={valorTriestado(alumno.autoriza_uso_imagen)}
                />
            </BloqueDetalle>

            {!alumno.activo && (
                <BloqueDetalle
                    title="Baja"
                    icon={UserRoundXIcon}
                    description="Información correspondiente a la baja del alumno."
                >
                    <DetalleCampo
                        label="Fecha de baja"
                        value={formatDate(alumno.fecha_baja, '—')}
                    />
                    <DetalleCampo
                        label="Motivo de baja"
                        value={alumno.motivo_baja}
                    />
                </BloqueDetalle>
            )}

            <BloqueDetalle title="Observaciones" icon={NotebookPenIcon}>
                <DetalleCampo
                    label="Observaciones generales"
                    value={
                        alumno.observaciones ? (
                            <span className="whitespace-pre-wrap">
                                {alumno.observaciones}
                            </span>
                        ) : null
                    }
                    className="md:col-span-2 xl:col-span-3"
                />
            </BloqueDetalle>
        </div>
    );
}
