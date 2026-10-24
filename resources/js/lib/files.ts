export function formatFileSize(bytes: number): string {
    const unidades = ['B', 'KB', 'MB', 'GB'];
    let cantidad = Math.max(0, bytes);
    let indice = 0;

    while (cantidad >= 1024 && indice < unidades.length - 1) {
        cantidad /= 1024;
        indice += 1;
    }

    return `${new Intl.NumberFormat('es-AR', { maximumFractionDigits: 1 }).format(cantidad)} ${unidades[indice]}`;
}
