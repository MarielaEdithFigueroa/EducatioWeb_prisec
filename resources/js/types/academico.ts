export type EstadoAnioLectivo = 'preparacion' | 'vigente' | 'cerrado';

export type Nivel = {
    id: number;
    activo?: boolean;
    codigo: string;
    descripcion: string;
    orden?: number;
};

export type AnioLectivo = {
    id: number;
    activo: boolean;
    anio: number;
    estado: EstadoAnioLectivo;
    grupos_count?: number;
    grupos_activos_count?: number;
};

export type CatalogoConNivel = {
    id: number;
    activo: boolean;
    nivel_id: number;
    descripcion: string;
    orden?: number;
    codigo?: string;
    grupos_count?: number;
    nivel?: Nivel;
};

export type GrupoAcademico = {
    id: number;
    activo: boolean;
    anio_lectivo_id: number;
    plan_estudio_id: number;
    curso_id: number;
    division_id: number;
    turno_id: number;
    anio_lectivo: AnioLectivo;
    plan_estudio: CatalogoConNivel;
    curso: CatalogoConNivel;
    division: CatalogoConNivel;
    turno: CatalogoConNivel;
};

export type FiltrosGrupos = {
    anio_lectivo_id: number | null;
    nivel_id: number | null;
    estado: 'activos' | 'inactivos' | 'todos';
};
