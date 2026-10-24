export function recorteCuadrado(
    ancho: number,
    alto: number,
    zoom: number,
    horizontal: number,
    vertical: number,
) {
    if (
        ![ancho, alto, zoom, horizontal, vertical].every(Number.isFinite) ||
        ancho <= 0 ||
        alto <= 0
    ) {
        throw new Error('Las dimensiones de la imagen no son válidas.');
    }

    const lado = Math.min(ancho, alto) / Math.max(1, zoom);
    const limitar = (valor: number) => Math.max(0, Math.min(1, valor));

    return {
        x: (ancho - lado) * limitar(horizontal),
        y: (alto - lado) * limitar(vertical),
        lado,
    };
}
