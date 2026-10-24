export type SeccionAlumno =
    | 'personal'
    | 'institucional'
    | 'responsables'
    | 'documentacion';

export type ResponsableVinculado = {
    id: number;
    alumno_responsable_id: number;
    activo: boolean;
    vinculo_activo: boolean;
    apellido: string;
    nombre: string;
    nombre_elegido: string | null;
    tipo_documento: string | null;
    numero_documento: string | null;
    email: string | null;
    telefono: string | null;
    tipo_vinculo: string;
    tipo_vinculo_id: number;
    orden_contacto: number;
    fecha_desde: string | null;
    fecha_hasta: string | null;
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
    observaciones: string | null;
    funciones: string[];
};

export type AlumnoDetalle = {
    id: number;
    activo: boolean;
    legajo: string;
    apellido: string;
    nombre: string;
    nombre_elegido: string | null;
    tipo_documento: string | null;
    numero_documento: string | null;
    cuilt: string | null;
    fecha_nacimiento: string | null;
    ciudad_nacimiento: string | null;
    provincia_nacimiento: string | null;
    nacionalidad: string | null;
    grupo_sanguineo: string | null;
    sexo_registral: string | null;
    genero: string | null;
    genero_autodescripcion: string | null;
    email: string | null;
    domicilio: string | null;
    ciudad: string | null;
    provincia: string | null;
    cpa: string | null;
    fecha_ingreso: string | null;
    fecha_inicio_cursado: string | null;
    libro: string | null;
    folio: string | null;
    fecha_baja: string | null;
    motivo_baja: string | null;
    autoriza_uso_imagen: boolean | null;
    observaciones: string | null;
    responsables: ResponsableVinculado[];
};

export type MotivoBaja = {
    id: number;
    nombre: string;
};
