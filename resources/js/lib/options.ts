import type { EureSelectOption } from '@/components/ui/eure';

type Catalogo = {
    id: number;
    descripcion: string;
    activo: boolean;
};

/**
 * Convierte una entidad de catálogo en opción para los `EureSelect`, sufijando
 * `(inactivo)` cuando corresponde (ver core.md: filtros sobre relaciones deben
 * incluir opciones inactivas para datos históricos).
 */
export function catalogoOption(item: Catalogo): EureSelectOption {
    return {
        value: item.id,
        label: item.activo
            ? item.descripcion
            : `${item.descripcion} (inactivo)`,
    };
}
