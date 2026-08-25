import { format, formatDistanceToNowStrict, isValid, parse } from 'date-fns';
import { es } from 'date-fns/locale';

export function formatDateTime(
    value: string | Date | null | undefined,
    emptyText = 'Sin datos',
): string {
    if (!value) {
        return emptyText;
    }

    const date = value instanceof Date ? value : new Date(value);

    if (date === null || !isValid(date)) {
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

    const date = parseDateOnly(value);

    if (date === null || !isValid(date)) {
        return emptyText;
    }

    return format(date, 'dd/MM/yyyy');
}

export function parseDateOnly(
    value: string | Date | null | undefined,
): Date | null {
    if (!value) {
        return null;
    }

    if (value instanceof Date) {
        return isValid(value) ? value : null;
    }

    const date = /^\d{4}-\d{2}-\d{2}$/.test(value)
        ? parse(value, 'yyyy-MM-dd', new Date())
        : new Date(value);

    return isValid(date) ? date : null;
}

export function toDateOnlyString(value: Date | null): string {
    return value && isValid(value) ? format(value, 'yyyy-MM-dd') : '';
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
