export function sanitizeMoneyInput(value: string): string {
    return value
        .replace(/\./g, ',')
        .replace(/[^\d,]/g, '')
        .replace(/,,+/g, ',')
        .replace(/^,/, '');
}

export function parseMoneyInput(value: string): number {
    const sanitized = sanitizeMoneyInput(value);

    if (!sanitized) {
        return 0;
    }

    const parsed = Number(sanitized.replace(',', '.'));

    return Number.isNaN(parsed) ? 0 : parsed;
}

export function formatMoneyArgentino(value: number): string {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
}
