import { router } from '@inertiajs/react';
import { useEffect } from 'react';

export function useUnsavedChanges(dirty: boolean, saving: boolean) {
    useEffect(() => {
        if (!dirty || saving) {
            return;
        }

        const mensaje =
            'Tenés cambios sin guardar. ¿Querés salir y descartarlos?';
        const quitar = router.on('before', (event) => {
            if (
                event.detail.visit.method === 'get' &&
                !window.confirm(mensaje)
            ) {
                event.preventDefault();
            }
        });
        const advertir = (event: BeforeUnloadEvent) => {
            event.preventDefault();
            event.returnValue = '';
        };
        window.addEventListener('beforeunload', advertir);

        return () => {
            quitar();
            window.removeEventListener('beforeunload', advertir);
        };
    }, [dirty, saving]);
}
