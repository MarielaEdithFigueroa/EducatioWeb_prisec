export type TipoVinculo = {
    id: number;
    nombre: string;
};

export type DatosVinculo = {
    vinculo_id: number | '';
    fecha_desde: string;
    fecha_hasta: string;
    es_responsable_legal: boolean;
    es_responsable_pedagogico: boolean;
    es_responsable_economico: boolean;
    recibe_comunicaciones: boolean;
    recibe_informes_academicos: boolean;
    recibe_facturacion: boolean;
    es_contacto_principal: boolean;
    es_contacto_emergencia: boolean;
    convive_con_alumno: boolean;
    autorizado_retirar: boolean;
    habilitado_portal: boolean;
    observaciones: string;
};

export const vinculoVacio = (): DatosVinculo => ({
    vinculo_id: '',
    fecha_desde: '',
    fecha_hasta: '',
    es_responsable_legal: false,
    es_responsable_pedagogico: false,
    es_responsable_economico: false,
    recibe_comunicaciones: false,
    recibe_informes_academicos: false,
    recibe_facturacion: false,
    es_contacto_principal: false,
    es_contacto_emergencia: false,
    convive_con_alumno: false,
    autorizado_retirar: false,
    habilitado_portal: false,
    observaciones: '',
});

export type ResponsableResumen = {
    id: number;
    activo: boolean;
    apellido: string;
    nombre: string;
    nombre_elegido: string | null;
    numero_documento: string | null;
    cuilt: string | null;
    email: string | null;
    telefono_e164: string | null;
    tipo_documento: { id: number; nombre: string } | null;
    alumnos_responsables: Array<{
        id: number;
        activo: boolean;
        tipo_vinculo?: { id: number; nombre: string } | null;
        alumno: {
            id: number;
            activo: boolean;
            legajo: string;
            apellido: string;
            nombre: string;
        };
    }>;
};
