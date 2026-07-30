import { Badge } from '@/components/ui/badge';

/** Badge unificado para el estado de baja lógica (`activo`) de las entidades. */
export default function ActivoBadge({ activo }: { activo: boolean }) {
    return (
        <Badge
            variant="outline"
            className={
                activo
                    ? 'border-green-500/40 bg-green-500/10 text-green-700 dark:text-green-400'
                    : 'border-red-500/40 bg-red-500/10 text-red-700 dark:text-red-400'
            }
        >
            {activo ? 'Activo' : 'Inactivo'}
        </Badge>
    );
}
