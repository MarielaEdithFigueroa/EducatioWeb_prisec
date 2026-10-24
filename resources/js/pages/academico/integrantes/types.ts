import {
    BriefcaseIcon,
    CakeIcon,
    ClipboardListIcon,
    MapPinIcon,
    UserIcon,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

export type FiltrosIntegrante = {
    buscar: string;
    estado: string;
    funcion_id: string;
};
export type DocumentoIntegrante = {
    tipo_documentacion_integrante_id: number;
    descripcion: string;
    activo: boolean;
    presentado: boolean;
    adjunto: {
        id: number;
        nombre_original: string;
        tamano_bytes: number;
    } | null;
    archivo?: File | null;
    eliminar_archivo?: boolean;
};
export type CamposTexto = {
    legajo: string;
    apellido: string;
    nombre: string;
    estado_civil: string;
    numero_documento: string;
    cuilt: string;
    sexo_registral: string;
    domicilio: string;
    cpa: string;
    telefono: string;
    celular: string;
    email: string;
    titulo_habilitante: string;
    numero_registro: string;
    art: string;
    obra_social: string;
    cuenta_bancaria: string;
    fecha_nacimiento: string;
    fecha_ingreso: string;
    fecha_residencia_provincia: string;
    tipo_documento_id: string;
    ciudad_id: string;
    ciudad_nacimiento_id: string;
    nacionalidad_id: string;
    horario: string;
};
export type CampoTexto = keyof CamposTexto;
export const TIPOS_TELEFONO_VACIOS = {
    telefono_es_fijo: false,
};
export type TipoTelefonoIntegrante = keyof typeof TIPOS_TELEFONO_VACIOS;
type TiposTelefonoIntegrante = Record<TipoTelefonoIntegrante, boolean>;
export type ReferenciaLaboralIntegrante = {
    id: number | null;
    clave?: string;
    lugar: string;
    cargo: string;
    tiempo: string;
    referencia_1_nombre: string;
    referencia_1_telefono: string;
    referencia_1_telefono_es_fijo: boolean;
    referencia_2_nombre: string;
    referencia_2_telefono: string;
    referencia_2_telefono_es_fijo: boolean;
    observaciones: string;
};
export type ContactoIntegrante = {
    id: number | null;
    clave?: string;
    nombre: string;
    telefono: string;
    telefono_es_fijo: boolean;
    vinculo: string;
    observaciones: string;
};
export type DatosIntegrante = CamposTexto &
    TiposTelefonoIntegrante & {
        referencias_laborales: ReferenciaLaboralIntegrante[];
        contactos: ContactoIntegrante[];
        referencias_laborales_bajas: number[];
        contactos_bajas: number[];
        funciones_ids: number[];
        tiene_aportes: boolean;
        documentacion: Array<{
            tipo_documentacion_integrante_id: number;
            presentado: boolean;
            archivo: File | null;
            eliminar_archivo: boolean;
            adjunto_id: number | null;
        }>;
        foto: File | null;
        eliminar_foto: boolean;
    };
export type OpcionCatalogo = {
    id: number;
    activo?: boolean;
    nombre?: string;
    descripcion?: string;
};
export type CiudadIntegrante = OpcionCatalogo & {
    provincia_id: number;
    codigo_postal: string | null;
    provincia: { id: number; nombre: string; activo: boolean };
};
export type FuncionEquipo = {
    id: number;
    codigo: string;
    descripcion: string;
    orden: number;
};
export type CatalogosIntegrante = {
    vinculos_contacto: string[];
    funciones_equipo: FuncionEquipo[];
    estados_civiles: string[];
    tipos_documento: OpcionCatalogo[];
    ciudades: CiudadIntegrante[];
    nacionalidades: OpcionCatalogo[];
};
export type Integrante = {
    [K in CampoTexto]: string | number | null;
} & TiposTelefonoIntegrante & {
        id: number;
        activo: boolean;
        funciones: FuncionEquipo[];
        referencias_laborales: ReferenciaLaboralIntegrante[];
        contactos: ContactoIntegrante[];
        tiene_aportes: boolean;
        tiene_foto: boolean;
        fecha_baja: string | null;
        motivo_baja: string | null;
        tipo_documento: OpcionCatalogo | null;
        ciudad: CiudadIntegrante | null;
        ciudad_nacimiento: CiudadIntegrante | null;
        nacionalidad: OpcionCatalogo | null;
    };
export type IntegranteFila = Pick<
    Integrante,
    | 'id'
    | 'activo'
    | 'legajo'
    | 'apellido'
    | 'nombre'
    | 'numero_documento'
    | 'cuilt'
    | 'funciones'
    | 'fecha_ingreso'
    | 'fecha_baja'
    | 'tipo_documento'
>;
export type SeccionIntegrante =
    | 'personal'
    | 'documentacion'
    | 'adjuntos'
    | 'otros';
export const SECCIONES: Array<{ value: SeccionIntegrante; label: string }> = [
    { value: 'personal', label: 'Datos personales' },
    { value: 'documentacion', label: 'Documentación' },
    { value: 'adjuntos', label: 'Adjuntos' },
    { value: 'otros', label: 'Otros datos' },
];
export type CampoIntegrante = {
    campo: CampoTexto;
    label: string;
    tipo?: 'fecha' | 'seleccion' | 'tel' | 'email' | 'multilinea';
    max?: number;
    /** Ocupa todo el ancho del grupo en la ficha. */
    ancho?: 'full';
};
export type GrupoCampos = {
    seccion: SeccionIntegrante;
    titulo: string;
    icono: LucideIcon;
    /** Ancho del grupo dentro de la sección de la ficha. */
    ancho: 'media' | 'completa';
    /** Columnas internas del grupo en la ficha, a partir de `lg`. */
    columnas: 2 | 3 | 4 | 5;
    campos: CampoIntegrante[];
};
/**
 * Configuración de la ficha y de la impresión: son listas de label/valor y se
 * benefician de generarse. El formulario **no** se deriva de acá — su layout
 * está escrito a mano en `partials/integrante-form.tsx`.
 */
export const GRUPOS_CAMPOS: GrupoCampos[] = [
    {
        seccion: 'personal',
        titulo: 'Identificación',
        icono: UserIcon,
        ancho: 'media',
        columnas: 3,
        campos: [
            { campo: 'legajo', label: 'Legajo', max: 30 },
            { campo: 'apellido', label: 'Apellido', max: 100 },
            { campo: 'nombre', label: 'Nombre', max: 100 },
            {
                campo: 'tipo_documento_id',
                label: 'Tipo de documento',
                tipo: 'seleccion',
            },
            {
                campo: 'numero_documento',
                label: 'Número de documento',
                max: 20,
            },
            { campo: 'cuilt', label: 'CUIL/CUIT', max: 11 },
            {
                campo: 'sexo_registral',
                label: 'Sexo registral',
                tipo: 'seleccion',
                max: 1,
            },
            { campo: 'estado_civil', label: 'Estado civil', tipo: 'seleccion' },
        ],
    },
    {
        seccion: 'personal',
        titulo: 'Nacimiento',
        icono: CakeIcon,
        ancho: 'media',
        columnas: 2,
        campos: [
            {
                campo: 'fecha_nacimiento',
                label: 'Fecha de nacimiento',
                tipo: 'fecha',
            },
            {
                campo: 'nacionalidad_id',
                label: 'Nacionalidad',
                tipo: 'seleccion',
            },
            {
                campo: 'ciudad_nacimiento_id',
                label: 'Ciudad de nacimiento',
                tipo: 'seleccion',
                ancho: 'full',
            },
        ],
    },
    {
        seccion: 'personal',
        titulo: 'Domicilio y contacto',
        icono: MapPinIcon,
        ancho: 'completa',
        columnas: 5,
        campos: [
            { campo: 'domicilio', label: 'Domicilio', max: 200 },
            { campo: 'ciudad_id', label: 'Localidad', tipo: 'seleccion' },
            { campo: 'cpa', label: 'Código postal / CPA', max: 10 },
            { campo: 'telefono', label: 'Teléfono', tipo: 'tel', max: 16 },
            {
                campo: 'email',
                label: 'Correo electrónico',
                tipo: 'email',
                max: 254,
            },
        ],
    },
    {
        seccion: 'otros',
        titulo: 'Información profesional',
        icono: BriefcaseIcon,
        ancho: 'completa',
        columnas: 3,
        campos: [
            {
                campo: 'fecha_ingreso',
                label: 'Ingreso a la institución',
                tipo: 'fecha',
            },
            {
                campo: 'titulo_habilitante',
                label: 'Título habilitante',
                max: 200,
            },
            { campo: 'numero_registro', label: 'Número de registro', max: 50 },
            {
                campo: 'horario',
                label: 'Turno / horario',
                tipo: 'multilinea',
                ancho: 'full',
            },
        ],
    },
    {
        seccion: 'otros',
        titulo: 'Información complementaria',
        icono: ClipboardListIcon,
        ancho: 'completa',
        columnas: 4,
        campos: [
            {
                campo: 'fecha_residencia_provincia',
                label: 'Reside en la provincia desde',
                tipo: 'fecha',
            },
            { campo: 'art', label: 'ART', max: 100 },
            { campo: 'obra_social', label: 'Obra social', max: 100 },
            { campo: 'cuenta_bancaria', label: 'Cuenta bancaria', max: 100 },
        ],
    },
];
/**
 * Sección donde vive cada campo. La usa el formulario para abrir la pestaña
 * correcta cuando el servidor devuelve errores de validación.
 */
export const SECCION_POR_CAMPO: Record<CampoTexto, SeccionIntegrante> = {
    legajo: 'personal',
    apellido: 'personal',
    nombre: 'personal',
    estado_civil: 'personal',
    numero_documento: 'personal',
    cuilt: 'personal',
    sexo_registral: 'personal',
    domicilio: 'personal',
    cpa: 'personal',
    telefono: 'personal',
    celular: 'personal',
    email: 'personal',
    fecha_nacimiento: 'personal',
    tipo_documento_id: 'personal',
    ciudad_id: 'personal',
    ciudad_nacimiento_id: 'personal',
    nacionalidad_id: 'personal',
    titulo_habilitante: 'otros',
    numero_registro: 'otros',
    art: 'otros',
    obra_social: 'otros',
    cuenta_bancaria: 'otros',
    fecha_ingreso: 'otros',
    fecha_residencia_provincia: 'otros',
    horario: 'otros',
};
export const TEXTO_VACIO: CamposTexto = {
    legajo: '',
    apellido: '',
    nombre: '',
    estado_civil: '',
    numero_documento: '',
    cuilt: '',
    sexo_registral: '',
    domicilio: '',
    cpa: '',
    telefono: '',
    celular: '',
    email: '',
    titulo_habilitante: '',
    numero_registro: '',
    art: '',
    obra_social: '',
    cuenta_bancaria: '',
    fecha_nacimiento: '',
    fecha_ingreso: '',
    fecha_residencia_provincia: '',
    tipo_documento_id: '',
    ciudad_id: '',
    ciudad_nacimiento_id: '',
    nacionalidad_id: '',
    horario: '',
};
export function contextoIntegrante(
    filters: FiltrosIntegrante,
    seccion?: SeccionIntegrante,
) {
    return { query: { filtros: filters, ...(seccion ? { seccion } : {}) } };
}
