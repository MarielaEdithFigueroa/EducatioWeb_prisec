export type CatalogoGrupo = {
    id: number;
    descripcion: string;
    activo: boolean;
    nivel_id?: number;
};

export type CatalogosGrupo = {
    niveles: CatalogoGrupo[];
    cursos: CatalogoGrupo[];
    divisiones: CatalogoGrupo[];
    turnos: CatalogoGrupo[];
    planes_estudio: CatalogoGrupo[];
};

export type Grupo = {
    id: number;
    activo: boolean;
    descripcion: string;
    ciclo_lectivo: number;
    nivel_id: number;
    curso_id: number;
    division_id: number;
    turno_id: number;
    plan_estudio_id: number | null;
    nivel: CatalogoGrupo;
    curso: CatalogoGrupo;
    division: CatalogoGrupo;
    turno: CatalogoGrupo;
    plan_estudio: CatalogoGrupo | null;
};

export type FiltrosGrupo = {
    ciclo_lectivo: string;
    nivel_id: string;
    curso_id: string;
    division_id: string;
    turno_id: string;
    plan_estudio_id: string;
    estado: string;
};

export const CAMPOS_GRUPO = [
    { campo: 'nivel_id', catalogo: 'niveles', label: 'Nivel' },
    { campo: 'curso_id', catalogo: 'cursos', label: 'Curso' },
    { campo: 'division_id', catalogo: 'divisiones', label: 'División' },
    { campo: 'turno_id', catalogo: 'turnos', label: 'Turno' },
    {
        campo: 'plan_estudio_id',
        catalogo: 'planes_estudio',
        label: 'Plan de estudio',
    },
] as const;

export function nombreCatalogo(registro: CatalogoGrupo | null): string {
    return registro
        ? `${registro.descripcion}${registro.activo ? '' : ' (inactivo)'}`
        : 'Sin plan';
}

export function consultaFiltros(filtros: FiltrosGrupo): Record<string, string> {
    return Object.fromEntries(
        Object.entries(filtros).filter(([, valor]) => valor !== ''),
    );
}
