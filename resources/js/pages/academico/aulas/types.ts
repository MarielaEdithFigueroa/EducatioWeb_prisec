export type Aula = {
    id: number;
    activo: boolean;
    descripcion: string;
    descripcion_larga: string | null;
    capacidad: number | null;
    es_especial: boolean;
    especialidad: string | null;
};
