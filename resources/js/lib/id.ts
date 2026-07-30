/**
 * Formatea un id numérico con ceros a la izquierda para mostrarlo en grillas
 * (columna `#`). La longitud acompaña a la entidad: catálogos chicos → 4.
 */
export function formatId(id: number | string, length = 4): string {
    return String(id).padStart(length, '0');
}
