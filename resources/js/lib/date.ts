import { format, formatDistanceToNowStrict, isValid } from 'date-fns';
import { es } from 'date-fns/locale';

export function formatDateTime(
    value: string | Date | null | undefined,
    emptyText = 'Sin datos',
): string {
    if (!value) {
        return emptyText;
    }

    const date = value instanceof Date ? value : new Date(value);

    if (!isValid(date)) {
        return emptyText;
    }

    return format(date, 'dd/MM/yyyy HH:mm');
}
export function formatDate(
    value: string | Date | null | undefined,
    emptyText = 'Sin datos',
): string {
    if (!value) {
        return emptyText;
    }

    const date = value instanceof Date ? value : new Date(value);

    if (!isValid(date)) {
        return emptyText;
    }

    return format(date, 'dd/MM/yyyy');
}
export function diffForHumans(
    value: string | Date | null | undefined,
    emptyText = 'Sin datos',
): string {
    if (!value) {
        return emptyText;
    }

    const date = value instanceof Date ? value : new Date(value);

    if (!isValid(date)) {
        return emptyText;
    }

    return formatDistanceToNowStrict(date, { addSuffix: true, locale: es });
}
