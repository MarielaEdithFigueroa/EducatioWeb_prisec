import type { RouteDefinition } from '@/wayfinder';

/** Forma común de los catálogos simples: código + descripción con baja lógica. */
export type CatalogoSimple = {
    id: number;
    activo: boolean;
    codigo: string;
    descripcion: string;
};

/**
 * Subconjunto de la acción Wayfinder de un catálogo simple que consumen los
 * componentes genéricos. Los objetos generados por Wayfinder son asignables.
 */
export type CatalogoSimpleController = {
    index: () => RouteDefinition<'get'>;
    create: () => RouteDefinition<'get'>;
    store: () => RouteDefinition<'post'>;
    edit: (id: number) => RouteDefinition<'get'>;
    update: (id: number) => RouteDefinition<'put'>;
    desactivar: (id: number) => RouteDefinition<'patch'>;
    reactivar: (id: number) => RouteDefinition<'patch'>;
};
