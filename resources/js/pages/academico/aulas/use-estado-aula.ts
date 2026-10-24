import { router } from '@inertiajs/react';
import { useState } from 'react';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import { desactivar, reactivar } from '@/routes/academico/aulas';
import type { Aula } from './types';

export function useEstadoAula() {
    const [procesando, setProcesando] = useState<number | null>(null);
    const confirmar = EureConfirmSwal();

    const cambiarEstado = async (aula: Aula) => {
        if (procesando !== null) {
            return;
        }

        const accion = aula.activo ? 'Desactivar' : 'Reactivar';
        const aceptado = await confirmar({
            title: `${accion} aula`,
            text: aula.activo
                ? `Se desactivará ${aula.descripcion}. Podrás reactivarla desde el listado.`
                : `Se reactivará ${aula.descripcion}.`,
            confirmText: accion,
        });

        if (!aceptado) {
            return;
        }

        setProcesando(aula.id);
        router.patch(
            (aula.activo ? desactivar : reactivar).url(aula.id),
            {},
            {
                preserveScroll: true,
                onFinish: () => setProcesando(null),
            },
        );
    };

    return { procesando, cambiarEstado };
}
