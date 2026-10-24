import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
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
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import { parseDateOnly, toDateOnlyString } from '@/lib/date';
import { desactivar, reactivar } from '@/routes/academico/integrantes';
import type { IntegranteFila } from './types';

export function useEstadoIntegrante() {
    const [seleccionado, setSeleccionado] = useState<IntegranteFila | null>(
        null,
    );
    const [reactivando, setReactivando] = useState<number | null>(null);
    const form = useForm({
        fecha_baja: toDateOnlyString(new Date()),
        motivo_baja: '',
    });
    const abrirBaja = (integrante: IntegranteFila) => {
        form.setData({
            fecha_baja: toDateOnlyString(new Date()),
            motivo_baja: '',
        });
        form.clearErrors();
        setSeleccionado(integrante);
    };
    const reincorporar = async (integrante: IntegranteFila) => {
        if (
            !(await EureConfirmSwal()({
                title: 'Reincorporar integrante',
                text: `“${integrante.apellido}, ${integrante.nombre}” volverá a estar activo.`,
                confirmText: 'Reincorporar',
            }))
        ) {
            return;
        }

        setReactivando(integrante.id);
        router.visit(reactivar(integrante.id), {
            preserveScroll: true,
            onFinish: () => setReactivando(null),
        });
    };
    const guardarBaja = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!seleccionado) {
            return;
        }

        form.submit(desactivar(seleccionado.id), {
            preserveScroll: true,
            onSuccess: () => {
                setSeleccionado(null);
                form.resetAndClearErrors();
            },
        });
    };
    const dialogo = (
        <Dialog
            open={seleccionado !== null}
            onOpenChange={(abierto) => {
                if (!abierto && !form.processing) {
                    setSeleccionado(null);
                }
            }}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Dar de baja</DialogTitle>
                    <DialogDescription>
                        {seleccionado?.apellido}, {seleccionado?.nombre}. El
                        legajo seguirá disponible para consulta histórica.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={guardarBaja} className="space-y-4" noValidate>
                    <EureDatePickerGroup
                        name="fecha_baja"
                        label="Fecha de baja"
                        value={parseDateOnly(form.data.fecha_baja)}
                        onChange={(fecha) =>
                            form.setData('fecha_baja', toDateOnlyString(fecha))
                        }
                        required
                        maxDate={new Date()}
                        error={Boolean(form.errors.fecha_baja)}
                        errorMessage={form.errors.fecha_baja}
                    />
                    <EureTextareaGroup
                        name="motivo_baja"
                        label="Motivo de baja"
                        value={form.data.motivo_baja}
                        onChange={(event) =>
                            form.setData('motivo_baja', event.target.value)
                        }
                        maxLength={500}
                        required
                        autoFocus
                        error={Boolean(form.errors.motivo_baja)}
                        errorMessage={form.errors.motivo_baja}
                    />
                    <DialogFooter>
                        <EureButtonSecondary
                            disabled={form.processing}
                            onClick={() => setSeleccionado(null)}
                        >
                            Cancelar
                        </EureButtonSecondary>
                        <EureButtonPrimary
                            type="submit"
                            loading={form.processing}
                        >
                            Confirmar baja
                        </EureButtonPrimary>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );

    return {
        abrirBaja,
        reincorporar,
        dialogo,
        procesando: form.processing || reactivando !== null,
    };
}
