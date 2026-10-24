import { Head, router, useForm } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    UsersIcon,
    PencilIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';
import AlumnoController from '@/actions/App/Http/Controllers/Academico/AlumnoController';
import EducatioCard from '@/components/educatio-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureDatePickerGroup from '@/components/ui/eure/EureDatePickerGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';
import { parseDateOnly, toDateOnlyString } from '@/lib/date';
import { formatId } from '@/lib/utils';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';
import type { TipoVinculo } from '../responsables/types';
import DatosInstitucionales from './partials/datos-institucionales';
import DatosPersonales from './partials/datos-personales';
import Documentacion from './partials/documentacion';
import PestanasAlumno from './partials/pestanas-alumno';
import Responsables from './partials/responsables';
import type {
    AlumnoDetalle,
    MotivoBaja,
    SeccionAlumno,
} from './partials/types';

type Props = {
    alumno: AlumnoDetalle;
    seccion: SeccionAlumno;
    motivosBaja: MotivoBaja[];
    tiposVinculo: TipoVinculo[];
    documentacion: Adjunto[];
    configAdjuntos: ConfigAdjuntos;
};

type DatosBaja = {
    fecha_baja: string;
    motivo_baja_id: number | '';
};

export default function AlumnoShow({
    alumno,
    seccion,
    motivosBaja,
    tiposVinculo,
    documentacion,
    configAdjuntos,
}: Props) {
    const [seccionActiva, setSeccionActiva] = useState<SeccionAlumno>(seccion);
    const [dialogoBajaAbierto, setDialogoBajaAbierto] = useState(false);
    const bajaForm = useForm<DatosBaja>({
        fecha_baja: toDateOnlyString(new Date()),
        motivo_baja_id: '',
    });

    const opcionesMotivo = useMemo<EureSelectOption[]>(
        () =>
            motivosBaja.map((motivo) => ({
                value: motivo.id,
                label: motivo.nombre,
            })),
        [motivosBaja],
    );

    const motivoSeleccionado =
        opcionesMotivo.find(
            (opcion) => opcion.value === bajaForm.data.motivo_baja_id,
        ) ?? null;

    useEffect(() => {
        window.history.replaceState(
            window.history.state,
            '',
            AlumnoController.show.url(alumno.id, {
                query: { seccion: seccionActiva },
            }),
        );
    }, [alumno.id, seccionActiva]);

    const documento = [alumno.tipo_documento, alumno.numero_documento]
        .filter(Boolean)
        .join(' ');

    const confirmarBaja = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        bajaForm.submit(AlumnoController.desactivar(alumno.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDialogoBajaAbierto(false);
                bajaForm.resetAndClearErrors();
            },
        });
    };

    const reactivar = async () => {
        const confirmado = await EureConfirmSwal()({
            title: 'Reactivar alumno',
            text: `“${alumno.apellido}, ${alumno.nombre}” volverá a estar disponible.`,
            confirmText: 'Reactivar',
        });

        if (!confirmado) {
            return;
        }

        router.visit(AlumnoController.reactivar(alumno.id), {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title={`${alumno.apellido}, ${alumno.nombre}`} />

            <div className="mx-auto flex w-full max-w-6xl flex-col gap-4">
                <EducatioCard className="flex flex-col justify-between gap-4 xl:flex-row xl:items-start">
                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <UsersIcon
                                className="size-7 shrink-0 text-educatio-icon"
                                aria-hidden="true"
                            />
                            <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                                {alumno.apellido}, {alumno.nombre}
                            </h1>
                            <Badge
                                variant="outline"
                                className={
                                    alumno.activo
                                        ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                        : 'border-eure-error/30 bg-eure-error/10 text-eure-error'
                                }
                            >
                                {alumno.activo ? 'Activo' : 'Inactivo'}
                            </Badge>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Alumno #{formatId(alumno.id, 6)}
                            {alumno.nombre_elegido
                                ? ` · Nombre elegido: ${alumno.nombre_elegido}`
                                : ''}
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Legajo: {alumno.legajo || '—'} · Documento:{' '}
                            {documento || '—'}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <EureButtonSecondary
                            type="button"
                            onClick={() =>
                                router.visit(AlumnoController.index())
                            }
                        >
                            <ArrowLeftIcon />
                            Volver
                        </EureButtonSecondary>
                        <EureButtonPrimary
                            type="button"
                            onClick={() =>
                                router.visit(AlumnoController.edit(alumno.id))
                            }
                        >
                            <PencilIcon />
                            Editar
                        </EureButtonPrimary>
                        {alumno.activo ? (
                            <Button
                                type="button"
                                variant="destructive"
                                onClick={() => setDialogoBajaAbierto(true)}
                            >
                                <Trash2Icon />
                                Desactivar
                            </Button>
                        ) : (
                            <EureButtonSecondary
                                type="button"
                                onClick={reactivar}
                            >
                                <RotateCcwIcon />
                                Reactivar
                            </EureButtonSecondary>
                        )}
                    </div>
                </EducatioCard>

                <PestanasAlumno
                    value={seccionActiva}
                    onValueChange={setSeccionActiva}
                />

                <div
                    id={`panel-alumno-${seccionActiva}`}
                    role="tabpanel"
                    aria-labelledby={`tab-alumno-${seccionActiva}`}
                    tabIndex={0}
                    className="focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    {seccionActiva === 'personal' && (
                        <DatosPersonales alumno={alumno} />
                    )}
                    {seccionActiva === 'institucional' && (
                        <DatosInstitucionales alumno={alumno} />
                    )}
                    {seccionActiva === 'responsables' && (
                        <Responsables
                            alumno={alumno}
                            responsables={alumno.responsables}
                            tiposVinculo={tiposVinculo}
                        />
                    )}
                    {seccionActiva === 'documentacion' && (
                        <Documentacion
                            alumnoId={alumno.id}
                            documentos={documentacion}
                            configuracion={configAdjuntos}
                        />
                    )}
                </div>
            </div>

            <Dialog
                open={dialogoBajaAbierto}
                onOpenChange={(abierto) => {
                    if (!abierto && !bajaForm.processing) {
                        setDialogoBajaAbierto(false);
                        bajaForm.resetAndClearErrors();
                    }
                }}
            >
                <DialogContent className="sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Desactivar alumno</DialogTitle>
                        <DialogDescription>
                            Indicá la fecha y el motivo de baja de{' '}
                            <strong>
                                {alumno.apellido}, {alumno.nombre}
                            </strong>
                            .
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={confirmarBaja} className="space-y-4">
                        <EureDatePickerGroup
                            label="Fecha de baja"
                            name="fecha_baja"
                            value={parseDateOnly(bajaForm.data.fecha_baja)}
                            onChange={(fecha) =>
                                bajaForm.setData(
                                    'fecha_baja',
                                    toDateOnlyString(fecha),
                                )
                            }
                            maxDate={new Date()}
                            error={Boolean(bajaForm.errors.fecha_baja)}
                            errorMessage={bajaForm.errors.fecha_baja}
                            required
                        />
                        <EureSelectGroup
                            label="Motivo de baja"
                            name="motivo_baja_id"
                            value={motivoSeleccionado}
                            onChange={(opcion) =>
                                bajaForm.setData(
                                    'motivo_baja_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesMotivo}
                            placeholder="Buscar y seleccionar un motivo"
                            error={Boolean(bajaForm.errors.motivo_baja_id)}
                            errorMessage={bajaForm.errors.motivo_baja_id}
                            required
                        />

                        <DialogFooter className="pt-2">
                            <EureButtonSecondary
                                type="button"
                                onClick={() => {
                                    setDialogoBajaAbierto(false);
                                    bajaForm.resetAndClearErrors();
                                }}
                                disabled={bajaForm.processing}
                            >
                                Cancelar
                            </EureButtonSecondary>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={bajaForm.processing}
                            >
                                Desactivar
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

AlumnoShow.layout = () => ({
    breadcrumbs: [
        {
            title: 'Alumnos',
            href: AlumnoController.index().url,
        },
        {
            title: 'Detalle',
            href: '#',
        },
    ],
});
