import { Link, useForm } from '@inertiajs/react';
import {
    BriefcaseIcon,
    CakeIcon,
    CameraIcon,
    ClipboardListIcon,
    FileTextIcon,
    MapPinIcon,
    SaveIcon,
    TagsIcon,
    UserIcon,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Checkbox } from '@/components/ui/checkbox';
import EureArchivoGroup from '@/components/ui/eure/EureArchivoGroup';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureDatePickerGroup from '@/components/ui/eure/EureDatePickerGroup';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureRecorteFoto from '@/components/ui/eure/EureRecorteFoto';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';
import EureTelefonoInputGroup from '@/components/ui/eure/EureTelefonoInputGroup';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import { useUnsavedChanges } from '@/hooks/use-unsaved-changes';
import { parseDateOnly, toDateOnlyString, yearsSince } from '@/lib/date';
import { cn } from '@/lib/utils';
import {
    foto,
    index,
    show,
    store,
    update,
} from '@/routes/academico/integrantes';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';
import AdjuntosIntegrante from '../adjuntos';
import ChecklistIntegrante, { contarPresentados } from '../checklist';
import PestanasIntegrante from '../pestanas';
import RelacionesIntegrante from '../relaciones';
import {
    contextoIntegrante,
    SECCION_POR_CAMPO,
    TEXTO_VACIO,
    TIPOS_TELEFONO_VACIOS,
} from '../types';
import type {
    CampoTexto,
    CatalogosIntegrante,
    DatosIntegrante,
    DocumentoIntegrante,
    FiltrosIntegrante,
    Integrante,
    OpcionCatalogo,
    SeccionIntegrante,
    TipoTelefonoIntegrante,
} from '../types';

export type IntegranteFormProps = {
    integrante: Integrante | null;
    documentacion: DocumentoIntegrante[];
    adjuntosLibres: Adjunto[];
    configAdjuntos: ConfigAdjuntos;
    filters: FiltrosIntegrante;
    seccion: SeccionIntegrante;
    catalogos: CatalogosIntegrante;
    configFoto: ConfigAdjuntos;
    configPdf: ConfigAdjuntos;
};

export function datosIniciales(
    integrante: Integrante | null,
    documentos: DocumentoIntegrante[],
): DatosIntegrante {
    const textos = { ...TEXTO_VACIO };
    const tiposTelefono = { ...TIPOS_TELEFONO_VACIOS };

    for (const campo of Object.keys(
        tiposTelefono,
    ) as TipoTelefonoIntegrante[]) {
        tiposTelefono[campo] = integrante?.[campo] ?? false;
    }

    for (const campo of Object.keys(textos) as CampoTexto[]) {
        textos[campo] = String(integrante?.[campo] ?? '');
    }

    return {
        ...textos,
        ...tiposTelefono,
        referencias_laborales: (integrante?.referencias_laborales ?? []).map(
            (fila) => ({ ...fila, clave: String(fila.id) }),
        ),
        contactos: (integrante?.contactos ?? []).map((fila) => ({
            ...fila,
            clave: String(fila.id),
        })),
        referencias_laborales_bajas: [],
        contactos_bajas: [],
        funciones_ids: integrante?.funciones.map((funcion) => funcion.id) ?? [],
        tiene_aportes: integrante?.tiene_aportes ?? false,
        foto: null,
        eliminar_foto: false,
        documentacion: documentos.map(
            ({ tipo_documentacion_integrante_id, presentado, adjunto }) => ({
                tipo_documentacion_integrante_id,
                presentado,
                archivo: null,
                eliminar_archivo: false,
                adjunto_id: adjunto?.id ?? null,
            }),
        ),
    };
}

function VistaPreviaFoto({ archivo }: { archivo: File }) {
    const imagen = useRef<HTMLImageElement>(null);
    useEffect(() => {
        const url = URL.createObjectURL(archivo);

        if (imagen.current) {
            imagen.current.src = url;
        }

        return () => URL.revokeObjectURL(url);
    }, [archivo]);

    return (
        <img
            ref={imagen}
            alt="Vista previa de la foto seleccionada"
            className="size-16 rounded-md border bg-muted object-cover"
        />
    );
}

/** Sección que corresponde al primer error devuelto por el servidor. */
function seccionDelError(campo: string | undefined): SeccionIntegrante {
    if (!campo) {
        return 'personal';
    }

    if (
        campo.startsWith('referencias_laborales') ||
        campo.startsWith('contactos')
    ) {
        return 'otros';
    }

    if (campo.startsWith('documentacion')) {
        return 'documentacion';
    }

    return SECCION_POR_CAMPO[campo as CampoTexto] ?? 'personal';
}

export default function IntegranteForm({
    integrante,
    documentacion,
    filters,
    seccion: seccionInicial,
    catalogos,
    configFoto,
    configPdf,
    adjuntosLibres,
    configAdjuntos,
}: IntegranteFormProps) {
    const form = useForm<DatosIntegrante>(
        datosIniciales(integrante, documentacion),
    );
    const [seccion, setSeccion] = useState<SeccionIntegrante>(seccionInicial);
    const [fotoParaRecortar, setFotoParaRecortar] = useState<File | null>(null);
    const [versionFoto, setVersionFoto] = useState(0);
    const [provincia, setProvincia] = useState(
        String(integrante?.ciudad?.provincia_id ?? ''),
    );
    const [provinciaNacimiento, setProvinciaNacimiento] = useState(
        String(integrante?.ciudad_nacimiento?.provincia_id ?? ''),
    );
    useUnsavedChanges(
        form.isDirty || fotoParaRecortar !== null,
        form.processing,
    );
    const volver = integrante
        ? show(integrante.id, contextoIntegrante(filters, seccion))
        : index({ query: filters });

    const opciones = (registros: OpcionCatalogo[]): EureSelectOption[] =>
        registros.map((item) => ({
            value: String(item.id),
            label: `${item.nombre ?? item.descripcion}${item.activo === false ? ' (inactivo)' : ''}`,
        }));
    const provincias = Array.from(
        new Map(
            catalogos.ciudades.map((ciudad) => [
                ciudad.provincia.id,
                ciudad.provincia,
            ]),
        ).values(),
    ).sort((a, b) => a.nombre.localeCompare(b.nombre, 'es-AR'));
    const ciudadesDe = (filtro: string): EureSelectOption[] =>
        catalogos.ciudades
            .filter(
                (ciudad) => !filtro || String(ciudad.provincia_id) === filtro,
            )
            .map((ciudad) => ({
                value: String(ciudad.id),
                label: `${ciudad.nombre} · ${ciudad.provincia.nombre}${ciudad.activo ? '' : ' (inactivo)'}`,
            }));
    const cambiarProvincia = (valor: string, nacimiento: boolean) => {
        if (nacimiento) {
            setProvinciaNacimiento(valor);
            form.setData('ciudad_nacimiento_id', '');
        } else {
            setProvincia(valor);
            form.setData((datos) => ({ ...datos, ciudad_id: '', cpa: '' }));
        }
    };
    const limpiarErroresRelacion = (prefijo: string) => {
        const campos = Object.keys(form.errors).filter((campo) =>
            campo.startsWith(prefijo),
        ) as Array<keyof typeof form.errors>;

        if (campos.length) {
            form.clearErrors(...campos);
        }
    };

    /* ----------------------------------------------------------------
       Plomería de los campos. El layout vive en el JSX de más abajo.
       ---------------------------------------------------------------- */
    const comunes = (campo: CampoTexto, label: string) => ({
        name: campo,
        label,
        error: Boolean(form.errors[campo]),
        errorMessage: form.errors[campo],
    });
    const texto = (
        campo: CampoTexto,
        label: string,
        opts: {
            max?: number;
            tipo?: 'text' | 'email';
            required?: boolean;
            autoFocus?: boolean;
        } = {},
    ): ReactNode => (
        <EureInputGroup
            {...comunes(campo, label)}
            type={opts.tipo ?? 'text'}
            value={form.data[campo]}
            onChange={(event) => form.setData(campo, event.target.value)}
            maxLength={opts.max}
            required={opts.required}
            autoFocus={opts.autoFocus}
        />
    );
    const seleccion = (
        campo: CampoTexto,
        label: string,
        lista: EureSelectOption[],
        onChange?: (valor: string) => void,
    ): ReactNode => (
        <EureSelectGroup
            {...comunes(campo, label)}
            options={lista}
            value={
                lista.find((opcion) => opcion.value === form.data[campo]) ??
                null
            }
            isClearable
            onChange={(opcion) => {
                const valor = String(opcion?.value ?? '');
                form.setData(campo, valor);
                onChange?.(valor);
            }}
        />
    );
    const fecha = (
        campo: CampoTexto,
        label: string,
        helperText?: string,
    ): ReactNode => (
        <EureDatePickerGroup
            {...comunes(campo, label)}
            value={parseDateOnly(form.data[campo])}
            onChange={(valor) => form.setData(campo, toDateOnlyString(valor))}
            helperText={helperText}
        />
    );
    const telefono = (campo: CampoTexto, label: string): ReactNode => {
        const campoTipo = `${campo}_es_fijo` as TipoTelefonoIntegrante;

        return (
            <EureTelefonoInputGroup
                {...comunes(campo, label)}
                value={form.data[campo]}
                onChange={(event) => form.setData(campo, event.target.value)}
                esLineaFija={form.data[campoTipo]}
                onEsLineaFijaChange={(fijo) => form.setData(campoTipo, fijo)}
            />
        );
    };

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const boton = (event.nativeEvent as SubmitEvent)
            .submitter as HTMLButtonElement | null;
        const continuar = boton?.value === 'continuar';
        form.transform((datos) => ({
            ...datos,
            referencias_laborales: datos.referencias_laborales.map((fila) => {
                const copia = { ...fila };
                delete copia.clave;

                return copia;
            }),
            contactos: datos.contactos.map((fila) => {
                const copia = { ...fila };
                delete copia.clave;

                return copia;
            }),
            funciones_ids: datos.funciones_ids.length
                ? datos.funciones_ids
                : '',
            continuar,
            filtros: filters,
            seccion,
            ...(integrante ? { _method: 'patch' } : {}),
        }));
        form.post(integrante ? update.url(integrante.id) : store.url(), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: (page) => {
                if (continuar) {
                    const actual = page.props.integrante as Integrante;
                    const documentos = page.props
                        .documentacion as DocumentoIntegrante[];
                    const datos = datosIniciales(actual, documentos);
                    form.setData(datos);
                    form.setDefaults(datos);
                    form.clearErrors();
                    setVersionFoto((valor) => valor + 1);
                }
            },
            onError: (errors) => {
                const primero = Object.keys(errors)[0];
                setSeccion(seccionDelError(primero));
                requestAnimationFrame(() =>
                    document
                        .getElementById(
                            primero?.startsWith('referencias_laborales')
                                ? 'referencias_laborales'
                                : primero?.startsWith('contactos')
                                  ? 'contactos'
                                  : primero?.startsWith('funciones_ids')
                                    ? `funcion-${catalogos.funciones_equipo[0]?.id}`
                                    : primero,
                        )
                        ?.focus(),
                );
            },
        });
    };

    const errorFunciones = Object.entries(form.errors).find(([campo]) =>
        campo.startsWith('funciones_ids'),
    )?.[1];
    const documentosChecklist = documentacion.map((item, i) => ({
        ...item,
        archivo: form.data.documentacion[i]?.archivo,
        eliminar_archivo: form.data.documentacion[i]?.eliminar_archivo,
        presentado: form.data.documentacion[i]?.presentado ?? false,
    }));

    const panel = (valor: SeccionIntegrante, contenido: ReactNode) => (
        <div
            key={valor}
            id={`panel-integrante-${valor}`}
            role="tabpanel"
            aria-labelledby={`tab-integrante-${valor}`}
            hidden={seccion !== valor}
            className="flex flex-col gap-4"
        >
            {contenido}
        </div>
    );

    return (
        <form onSubmit={guardar} noValidate className="flex flex-col gap-3">
            <PestanasIntegrante
                value={seccion}
                onChange={setSeccion}
                contadores={{
                    documentacion: contarPresentados(documentosChecklist),
                }}
            />

            {form.hasErrors && (
                <p role="alert" className="text-sm text-eure-error">
                    Revisá los campos señalados. Los datos ingresados se
                    conservaron.
                </p>
            )}

            {/* ---------------------------- Datos personales --------------------------- */}
            {panel(
                'personal',
                <>
                    <EducatioCard title="Identificación" icon={UserIcon}>
                        <div className="flex flex-col gap-4">
                            <div className="grid items-start gap-4 sm:grid-cols-2">
                                {texto('apellido', 'Apellido', {
                                    max: 100,
                                    required: true,
                                    autoFocus: seccionInicial === 'personal',
                                })}
                                {texto('nombre', 'Nombre', {
                                    max: 100,
                                    required: true,
                                })}
                            </div>
                            <div className="grid items-start gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div className="w-full max-w-48">
                                    {texto('legajo', 'Legajo', {
                                        max: 30,
                                        required: true,
                                    })}
                                </div>
                                {seleccion(
                                    'tipo_documento_id',
                                    'Tipo de documento',
                                    opciones(catalogos.tipos_documento),
                                )}
                                {texto(
                                    'numero_documento',
                                    'Número de documento',
                                    {
                                        max: 20,
                                    },
                                )}
                            </div>
                            <div className="grid items-start gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {texto('cuilt', 'CUIL/CUIT', { max: 11 })}
                                {seleccion(
                                    'estado_civil',
                                    'Estado civil',
                                    catalogos.estados_civiles.map((estado) => ({
                                        value: estado,
                                        label: estado,
                                    })),
                                )}
                                {seleccion('sexo_registral', 'Sexo registral', [
                                    { value: 'F', label: 'Femenino' },
                                    { value: 'M', label: 'Masculino' },
                                    { value: 'X', label: 'X' },
                                ])}
                            </div>
                        </div>
                    </EducatioCard>

                    <EducatioCard title="Nacimiento" icon={CakeIcon}>
                        <div className="grid items-start gap-4 sm:grid-cols-2">
                            {fecha(
                                'fecha_nacimiento',
                                'Fecha de nacimiento',
                                `Edad: ${yearsSince(form.data.fecha_nacimiento) ?? '—'} años`,
                            )}
                            {seleccion(
                                'nacionalidad_id',
                                'Nacionalidad',
                                opciones(catalogos.nacionalidades),
                            )}
                            <EureSelectGroup
                                name="provincia_nacimiento"
                                label="Provincia"
                                options={opciones(provincias)}
                                value={
                                    opciones(provincias).find(
                                        (item) =>
                                            item.value === provinciaNacimiento,
                                    ) ?? null
                                }
                                onChange={(opcion) =>
                                    cambiarProvincia(
                                        String(opcion?.value ?? ''),
                                        true,
                                    )
                                }
                                isClearable
                            />
                            {seleccion(
                                'ciudad_nacimiento_id',
                                'Ciudad',
                                ciudadesDe(provinciaNacimiento),
                            )}
                        </div>
                    </EducatioCard>

                    <EducatioCard
                        title="Domicilio y contacto"
                        icon={MapPinIcon}
                    >
                        <div className="flex flex-col gap-4">
                            {texto('domicilio', 'Domicilio', { max: 200 })}
                            <div className="grid items-start gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_9rem]">
                                <EureSelectGroup
                                    name="provincia"
                                    label="Provincia"
                                    options={opciones(provincias)}
                                    value={
                                        opciones(provincias).find(
                                            (item) => item.value === provincia,
                                        ) ?? null
                                    }
                                    onChange={(opcion) =>
                                        cambiarProvincia(
                                            String(opcion?.value ?? ''),
                                            false,
                                        )
                                    }
                                    isClearable
                                />
                                {seleccion(
                                    'ciudad_id',
                                    'Localidad',
                                    ciudadesDe(provincia),
                                    (valor) =>
                                        form.setData(
                                            'cpa',
                                            catalogos.ciudades.find(
                                                (ciudad) =>
                                                    String(ciudad.id) === valor,
                                            )?.codigo_postal ?? '',
                                        ),
                                )}
                                <div className="w-full max-w-36">
                                    {texto('cpa', 'Código postal / CPA', {
                                        max: 10,
                                    })}
                                </div>
                            </div>
                            <div className="grid items-start gap-4 sm:grid-cols-2">
                                {telefono('telefono', 'Teléfono')}
                                {texto('email', 'Correo electrónico', {
                                    max: 254,
                                    tipo: 'email',
                                })}
                            </div>
                        </div>
                    </EducatioCard>

                    <EducatioCard
                        title="Funciones"
                        icon={TagsIcon}
                        description={`${form.data.funciones_ids.length} ${
                            form.data.funciones_ids.length === 1
                                ? 'seleccionada'
                                : 'seleccionadas'
                        }. Podés elegir más de una.`}
                    >
                        {catalogos.funciones_equipo.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                El catálogo de funciones está vacío.
                            </p>
                        ) : (
                            <div
                                className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                                aria-describedby={
                                    errorFunciones
                                        ? 'funciones-error'
                                        : undefined
                                }
                            >
                                {catalogos.funciones_equipo.map((funcion) => {
                                    const elegida =
                                        form.data.funciones_ids.includes(
                                            funcion.id,
                                        );

                                    return (
                                        <label
                                            key={funcion.id}
                                            htmlFor={`funcion-${funcion.id}`}
                                            className={cn(
                                                'flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2.5 text-sm transition-colors focus-within:ring-2 focus-within:ring-ring',
                                                elegida
                                                    ? 'border-primary/40 bg-primary/8 font-semibold text-primary'
                                                    : 'border-border text-muted-foreground hover:bg-muted',
                                            )}
                                        >
                                            <Checkbox
                                                id={`funcion-${funcion.id}`}
                                                name="funciones_ids"
                                                value={String(funcion.id)}
                                                className="size-3.5"
                                                aria-invalid={Boolean(
                                                    errorFunciones,
                                                )}
                                                checked={elegida}
                                                onCheckedChange={(marcada) =>
                                                    form.setData(
                                                        'funciones_ids',
                                                        marcada === true
                                                            ? [
                                                                  ...form.data
                                                                      .funciones_ids,
                                                                  funcion.id,
                                                              ]
                                                            : form.data.funciones_ids.filter(
                                                                  (id) =>
                                                                      id !==
                                                                      funcion.id,
                                                              ),
                                                    )
                                                }
                                            />
                                            {funcion.descripcion}
                                        </label>
                                    );
                                })}
                            </div>
                        )}
                        {errorFunciones && (
                            <p
                                id="funciones-error"
                                role="alert"
                                className="mt-2 text-sm text-eure-error"
                            >
                                {errorFunciones}
                            </p>
                        )}
                    </EducatioCard>
                    <EducatioCard title="Foto" icon={CameraIcon}>
                        <div className="flex max-w-xl flex-col items-start gap-4 sm:flex-row">
                            {form.data.foto ? (
                                <VistaPreviaFoto archivo={form.data.foto} />
                            ) : integrante?.tiene_foto &&
                              !form.data.eliminar_foto ? (
                                <img
                                    src={foto.url(integrante.id, {
                                        query: { version: versionFoto },
                                    })}
                                    alt={`Foto de ${integrante.nombre} ${integrante.apellido}`}
                                    className="size-16 rounded-md border bg-muted object-cover"
                                />
                            ) : (
                                <span className="grid size-16 shrink-0 place-items-center rounded-md border border-dashed bg-muted text-xs text-muted-foreground">
                                    Sin foto
                                </span>
                            )}
                            <div className="flex w-full min-w-0 flex-1 flex-col gap-2">
                                <EureArchivoGroup
                                    name="foto"
                                    configuracion={configFoto}
                                    value={form.data.foto}
                                    disabled={form.processing}
                                    error={form.errors.foto}
                                    onChange={(archivo) => {
                                        if (archivo) {
                                            setFotoParaRecortar(archivo);
                                        } else {
                                            form.setData('foto', null);
                                        }
                                    }}
                                />
                                {integrante?.tiene_foto && (
                                    <EureButtonSecondary
                                        type="button"
                                        onClick={async () => {
                                            if (form.data.eliminar_foto) {
                                                form.setData(
                                                    'eliminar_foto',
                                                    false,
                                                );

                                                return;
                                            }

                                            if (
                                                await EureConfirmSwal()({
                                                    title: 'Eliminar foto',
                                                    text: 'La foto se eliminará al guardar el legajo.',
                                                    confirmText: 'Eliminar',
                                                })
                                            ) {
                                                form.setData((datos) => ({
                                                    ...datos,
                                                    foto: null,
                                                    eliminar_foto: true,
                                                }));
                                            }
                                        }}
                                    >
                                        {form.data.eliminar_foto
                                            ? 'Conservar foto actual'
                                            : 'Quitar foto'}
                                    </EureButtonSecondary>
                                )}
                            </div>
                        </div>
                        <p className="mt-2 text-xs text-muted-foreground">
                            JPG, PNG o WebP, cuadrada. Elegí el encuadre antes
                            de guardar.
                        </p>
                        {form.errors.eliminar_foto && (
                            <p className="mt-1 text-sm text-eure-error">
                                {form.errors.eliminar_foto}
                            </p>
                        )}
                    </EducatioCard>
                </>,
            )}

            {/* ----------------------------- Documentación ----------------------------- */}
            {panel(
                'documentacion',
                <EducatioCard
                    title="Documentación del legajo"
                    icon={FileTextIcon}
                    description={`${contarPresentados(documentosChecklist)} presentados`}
                >
                    <ChecklistIntegrante
                        documentos={documentosChecklist}
                        errors={form.errors}
                        integranteId={integrante?.id}
                        editablePdf
                        configuracionPdf={configPdf}
                        disabled={form.processing}
                        onArchivo={(indice, archivo, eliminar) =>
                            form.setData(
                                'documentacion',
                                form.data.documentacion.map((item, i) =>
                                    i === indice
                                        ? {
                                              ...item,
                                              archivo,
                                              eliminar_archivo: eliminar,
                                              presentado: archivo
                                                  ? true
                                                  : item.presentado,
                                          }
                                        : item,
                                ),
                            )
                        }
                        onChange={(indice, presentado) =>
                            form.setData(
                                'documentacion',
                                form.data.documentacion.map((item, i) =>
                                    i === indice
                                        ? { ...item, presentado }
                                        : item,
                                ),
                            )
                        }
                    />
                </EducatioCard>,
            )}

            {/* -------------------------------- Adjuntos ------------------------------- */}
            {panel(
                'adjuntos',
                <EducatioCard>
                    <AdjuntosIntegrante
                        integranteId={integrante?.id}
                        documentos={adjuntosLibres}
                        configuracion={configAdjuntos}
                    />
                </EducatioCard>,
            )}

            {/* ------------------------------ Otros datos ------------------------------ */}
            {panel(
                'otros',
                <>
                    <EducatioCard
                        title="Información profesional"
                        icon={BriefcaseIcon}
                    >
                        <div className="flex flex-col gap-3">
                            <div className="grid items-start gap-3 sm:grid-cols-2 lg:grid-cols-[10rem_minmax(0,1fr)_10rem]">
                                {fecha(
                                    'fecha_ingreso',
                                    'Ingreso a la institución',
                                    `Antigüedad: ${yearsSince(form.data.fecha_ingreso) ?? '—'} años`,
                                )}
                                {texto(
                                    'titulo_habilitante',
                                    'Título habilitante',
                                    { max: 200 },
                                )}
                                {texto('numero_registro', 'Nro. de registro', {
                                    max: 50,
                                })}
                            </div>
                            <label
                                htmlFor="tiene_aportes"
                                className="flex w-fit items-center gap-2 text-sm"
                            >
                                <Checkbox
                                    id="tiene_aportes"
                                    checked={form.data.tiene_aportes}
                                    onCheckedChange={(valor) =>
                                        form.setData(
                                            'tiene_aportes',
                                            valor === true,
                                        )
                                    }
                                />
                                Tiene aportes
                            </label>
                            <EureTextareaGroup
                                {...comunes('horario', 'Turno / horario')}
                                value={form.data.horario}
                                onChange={(event) =>
                                    form.setData('horario', event.target.value)
                                }
                                rows={3}
                                maxLength={2000}
                            />
                        </div>
                    </EducatioCard>

                    <EducatioCard
                        title="Información complementaria"
                        icon={ClipboardListIcon}
                    >
                        <div className="grid items-start gap-4 sm:grid-cols-2">
                            {fecha(
                                'fecha_residencia_provincia',
                                'Reside en la provincia desde',
                            )}
                            {texto('art', 'ART', { max: 100 })}
                            {texto('obra_social', 'Obra social', { max: 100 })}
                            {texto('cuenta_bancaria', 'Cuenta bancaria', {
                                max: 100,
                            })}
                        </div>
                    </EducatioCard>

                    <RelacionesIntegrante
                        referencias={form.data.referencias_laborales}
                        contactos={form.data.contactos}
                        vinculos={catalogos.vinculos_contacto}
                        errores={form.errors}
                        disabled={form.processing}
                        onReferenciasChange={(filas, bajaId) => {
                            form.setData((datos) => ({
                                ...datos,
                                referencias_laborales: filas,
                                referencias_laborales_bajas: bajaId
                                    ? [
                                          ...datos.referencias_laborales_bajas,
                                          bajaId,
                                      ]
                                    : datos.referencias_laborales_bajas,
                            }));
                            limpiarErroresRelacion('referencias_laborales');
                        }}
                        onContactosChange={(filas, bajaId) => {
                            form.setData((datos) => ({
                                ...datos,
                                contactos: filas,
                                contactos_bajas: bajaId
                                    ? [...datos.contactos_bajas, bajaId]
                                    : datos.contactos_bajas,
                            }));
                            limpiarErroresRelacion('contactos');
                        }}
                    />
                </>,
            )}

            {form.progress && (
                <p role="status" className="text-sm">
                    Guardando archivos: {form.progress.percentage}%
                </p>
            )}

            <div className="sticky bottom-0 z-10 flex flex-wrap items-center gap-2 border-t bg-background py-3">
                <EureButtonPrimary
                    type="submit"
                    value="guardar"
                    loading={form.processing}
                >
                    <SaveIcon aria-hidden="true" />
                    {integrante ? 'Guardar cambios' : 'Crear integrante'}
                </EureButtonPrimary>
                <EureButtonSecondary
                    type="submit"
                    value="continuar"
                    disabled={form.processing}
                >
                    Guardar y continuar
                </EureButtonSecondary>
                <span className="flex-1" />
                <EureButtonSecondary asChild>
                    <Link href={volver}>Cancelar</Link>
                </EureButtonSecondary>
            </div>

            {fotoParaRecortar && (
                <EureRecorteFoto
                    archivo={fotoParaRecortar}
                    maxBytes={configFoto.max_bytes}
                    onCancelar={() => setFotoParaRecortar(null)}
                    onConfirmar={(archivo) => {
                        form.setData((datos) => ({
                            ...datos,
                            foto: archivo,
                            eliminar_foto: false,
                        }));
                        form.clearErrors('foto', 'eliminar_foto');
                        setFotoParaRecortar(null);
                    }}
                />
            )}
        </form>
    );
}
