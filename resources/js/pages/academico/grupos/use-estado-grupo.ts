import { router } from '@inertiajs/react';
import { useState } from 'react';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import { desactivar, reactivar } from '@/routes/academico/grupos';
import type { Grupo } from './types';

export function useEstadoGrupo() {
    const [procesando, setProcesando] = useState<number | null>(null);
    const confirmar = EureConfirmSwal();

    const cambiarEstado = async (grupo: Grupo) => {
        if (procesando !== null) {
            return;
        }

        const accion = grupo.activo ? 'Desactivar' : 'Reactivar';
        const aceptado = await confirmar({
            title: `${accion} grupo`,
            text: grupo.activo
                ? `Se desactivará ${grupo.descripcion}. Podrás reactivarlo desde el listado.`
                : `Se reactivará ${grupo.descripcion}.`,
            confirmText: accion,
        });

        if (!aceptado) {
            return;
        }

        setProcesando(grupo.id);
        router.patch(
            (grupo.activo ? desactivar : reactivar).url(grupo.id),
            {},
            {
                preserveScroll: true,
                onFinish: () => setProcesando(null),
            },
        );
    };

    return { procesando, cambiarEstado };
}
