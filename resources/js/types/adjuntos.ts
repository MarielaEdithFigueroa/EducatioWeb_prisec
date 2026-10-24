export type Adjunto = {
    id: number;
    activo: boolean;
    nombre_original: string;
    mime: string;
    tamano_bytes: number;
    observaciones: string | null;
    fecha_registro: string;
    usuario: string;
};

export type ConfigAdjuntos = {
    max_bytes: number;
    extensiones: string[];
};
