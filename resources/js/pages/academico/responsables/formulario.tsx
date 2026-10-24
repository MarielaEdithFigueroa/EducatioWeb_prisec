import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    SaveIcon,
    UserCogIcon,
    IdCardIcon,
    CalendarDaysIcon,
    MapPinIcon,
    BriefcaseBusinessIcon,
    NotebookPenIcon,
    LinkIcon,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo } from 'react';
import AlumnoController from '@/actions/App/Http/Controllers/Academico/AlumnoController';
import ResponsableController from '@/actions/App/Http/Controllers/Academico/ResponsableController';
import EducatioCard from '@/components/educatio-card';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureDatePickerGroup from '@/components/ui/eure/EureDatePickerGroup';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';
import EureTelefonoInputGroup from '@/components/ui/eure/EureTelefonoInputGroup';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import { parseDateOnly, toDateOnlyString } from '@/lib/date';
import FormularioVinculo from './formulario-vinculo';
import type { DatosVinculo, TipoVinculo } from './types';
import { vinculoVacio } from './types';

type Alumno = { id: number; legajo: string; apellido: string; nombre: string };
type Catalogo = { id: number; nombre: string };
type Ciudad = Catalogo & {
    activo: boolean;
    provincia_id: number;
    codigo_postal: string | null;
    provincia: Catalogo;
};

type DatosResponsable = {
    apellido: string;
    nombre: string;
    nombre_elegido: string;
    tipo_documento_id: number | '';
    numero_documento: string;
    cuilt: string;
    fecha_nacimiento: string;
    sexo_registral: string;
    genero: string;
    genero_autodescripcion: string;
    email: string;
    telefono: string;
    telefono_es_fijo: boolean;
    domicilio: string;
    ciudad_id: number | '';
    cpa: string;
    profesion: string;
    domicilio_laboral: string;
    ciudad_laboral_id: number | '';
    cpa_laboral: string;
    telefono_laboral: string;
    telefono_laboral_es_fijo: boolean;
    observaciones: string;
};

type Formulario = { responsable: DatosResponsable; vinculo: DatosVinculo };

type Props = {
    alumno: Alumno | null;
    responsable:
        | ({ id: number; activo: boolean } & {
              [K in keyof DatosResponsable]: DatosResponsable[K] | null;
          })
        | null;
    tiposDocumento: Catalogo[];
    ciudades: Ciudad[];
    tiposVinculo: TipoVinculo[];
};

const OPCIONES_SEXO: EureSelectOption[] = [
    { value: 'F', label: 'Femenino' },
    { value: 'M', label: 'Masculino' },
    { value: 'X', label: 'X' },
];

const OPCIONES_GENERO: EureSelectOption[] = [
    { value: 'Mujer', label: 'Mujer' },
    { value: 'Varón', label: 'Varón' },
    { value: 'No binario', label: 'No binario' },
    { value: 'Otra identidad', label: 'Otra identidad' },
    { value: 'Prefiere no informar', label: 'Prefiere no informar' },
];

const buscarOpcion = (
    opciones: EureSelectOption[],
    valor: string | number | '',
) => opciones.find((opcion) => opcion.value === valor) ?? null;

export default function FormularioResponsable({
    alumno,
    responsable,
    tiposDocumento,
    ciudades,
    tiposVinculo,
}: Props) {
    const form = useForm<Formulario>({
        responsable: {
            apellido: responsable?.apellido ?? '',
            nombre: responsable?.nombre ?? '',
            nombre_elegido: responsable?.nombre_elegido ?? '',
            tipo_documento_id: responsable?.tipo_documento_id ?? '',
            numero_documento: responsable?.numero_documento ?? '',
            cuilt: responsable?.cuilt ?? '',
            fecha_nacimiento: responsable?.fecha_nacimiento ?? '',
            sexo_registral: responsable?.sexo_registral ?? '',
            genero: responsable?.genero ?? '',
            genero_autodescripcion: responsable?.genero_autodescripcion ?? '',
            email: responsable?.email ?? '',
            telefono: responsable?.telefono ?? '',
            telefono_es_fijo: responsable?.telefono_es_fijo ?? false,
            domicilio: responsable?.domicilio ?? '',
            ciudad_id: responsable?.ciudad_id ?? '',
            cpa: responsable?.cpa ?? '',
            profesion: responsable?.profesion ?? '',
            domicilio_laboral: responsable?.domicilio_laboral ?? '',
            ciudad_laboral_id: responsable?.ciudad_laboral_id ?? '',
            cpa_laboral: responsable?.cpa_laboral ?? '',
            telefono_laboral: responsable?.telefono_laboral ?? '',
            telefono_laboral_es_fijo:
                responsable?.telefono_laboral_es_fijo ?? false,
            observaciones: responsable?.observaciones ?? '',
        },
        vinculo: {
            ...vinculoVacio(),
            fecha_desde: toDateOnlyString(new Date()),
        },
    });

    const opcionesTipoDocumento = useMemo<EureSelectOption[]>(
        () =>
            tiposDocumento.map((tipo) => ({
                value: tipo.id,
                label: tipo.nombre,
            })),
        [tiposDocumento],
    );
    const opcionesCiudad = useMemo<EureSelectOption[]>(
        () =>
            ciudades.map((ciudad) => ({
                value: ciudad.id,
                label: `${ciudad.nombre} · ${ciudad.provincia.nombre}${ciudad.activo ? '' : ' (inactiva)'}`,
            })),
        [ciudades],
    );
    const errors = form.errors as unknown as Record<string, string | undefined>;

    const cambiarResponsable = <K extends keyof DatosResponsable>(
        campo: K,
        valor: DatosResponsable[K],
    ) =>
        form.setData('responsable', {
            ...form.data.responsable,
            [campo]: valor,
        });

    const seleccionarCiudad = (
        campo: 'ciudad_id' | 'ciudad_laboral_id',
        campoCpa: 'cpa' | 'cpa_laboral',
        opcion: EureSelectOption | null,
    ) => {
        const ciudadId = opcion === null ? '' : Number(opcion.value);
        const siguiente = { ...form.data.responsable, [campo]: ciudadId };

        if (siguiente[campoCpa] === '' && ciudadId !== '') {
            siguiente[campoCpa] =
                ciudades.find((ciudad) => ciudad.id === ciudadId)
                    ?.codigo_postal ?? '';
        }

        form.setData('responsable', siguiente);
    };

    const guardar = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (responsable) {
            form.submit(ResponsableController.update(responsable.id), {
                preserveScroll: true,
            });
        } else if (alumno) {
            form.submit(ResponsableController.store(alumno.id), {
                preserveScroll: true,
            });
        }
    };

    const volver = responsable
        ? ResponsableController.show(responsable.id)
        : alumno
          ? AlumnoController.show(alumno.id, {
                query: { seccion: 'responsables' },
            })
          : ResponsableController.index();
    const titulo = responsable ? 'Editar responsable' : 'Nuevo responsable';

    return (
        <>
            <Head title={titulo} />
            <form
                onSubmit={guardar}
                className="mx-auto flex w-full max-w-6xl flex-col gap-4"
            >
                <EducatioCard>
                    <header className="flex items-center gap-3">
                        <UserCogIcon
                            className="size-8 shrink-0 text-educatio-icon"
                            aria-hidden="true"
                        />
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                                {titulo}
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {responsable
                                    ? 'Los cambios se aplican a esta persona en todos sus vínculos.'
                                    : alumno &&
                                      `Se creará y vinculará con ${alumno.apellido}, ${alumno.nombre} · Legajo ${alumno.legajo}.`}
                            </p>
                        </div>
                    </header>
                </EducatioCard>

                <EducatioCard
                    title="Identificación"
                    icon={IdCardIcon}
                    description="Nombres y documentación personal."
                >
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <EureInputGroup
                            label="Apellido"
                            name="apellido"
                            value={form.data.responsable.apellido}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'apellido',
                                    event.target.value,
                                )
                            }
                            error={Boolean(errors['responsable.apellido'])}
                            errorMessage={errors['responsable.apellido']}
                            maxLength={100}
                            autoFocus
                            required
                        />
                        <EureInputGroup
                            label="Nombre"
                            name="nombre"
                            value={form.data.responsable.nombre}
                            onChange={(event) =>
                                cambiarResponsable('nombre', event.target.value)
                            }
                            error={Boolean(errors['responsable.nombre'])}
                            errorMessage={errors['responsable.nombre']}
                            maxLength={100}
                            required
                        />
                        <EureInputGroup
                            label="Nombre elegido"
                            name="nombre_elegido"
                            value={form.data.responsable.nombre_elegido}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'nombre_elegido',
                                    event.target.value,
                                )
                            }
                            error={Boolean(
                                errors['responsable.nombre_elegido'],
                            )}
                            errorMessage={errors['responsable.nombre_elegido']}
                            maxLength={100}
                        />
                        <EureSelectGroup
                            label="Tipo de documento"
                            name="tipo_documento_id"
                            value={buscarOpcion(
                                opcionesTipoDocumento,
                                form.data.responsable.tipo_documento_id,
                            )}
                            onChange={(opcion) =>
                                cambiarResponsable(
                                    'tipo_documento_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesTipoDocumento}
                            placeholder="Buscar tipo"
                            error={Boolean(
                                errors['responsable.tipo_documento_id'],
                            )}
                            errorMessage={
                                errors['responsable.tipo_documento_id']
                            }
                            isClearable
                        />
                        <EureInputGroup
                            label="Número de documento"
                            name="numero_documento"
                            value={form.data.responsable.numero_documento}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'numero_documento',
                                    event.target.value.toLocaleUpperCase(
                                        'es-AR',
                                    ),
                                )
                            }
                            error={Boolean(
                                errors['responsable.numero_documento'],
                            )}
                            errorMessage={
                                errors['responsable.numero_documento']
                            }
                            maxLength={20}
                        />
                        <EureInputGroup
                            label="CUIL/T"
                            name="cuilt"
                            value={form.data.responsable.cuilt}
                            onChange={(event) =>
                                cambiarResponsable('cuilt', event.target.value)
                            }
                            error={Boolean(errors['responsable.cuilt'])}
                            errorMessage={errors['responsable.cuilt']}
                            mask="__-________-_"
                            replacement={{ _: /\d/ }}
                        />
                    </div>
                </EducatioCard>

                <EducatioCard
                    title="Nacimiento e identidad"
                    icon={CalendarDaysIcon}
                >
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <EureDatePickerGroup
                            label="Fecha de nacimiento"
                            name="fecha_nacimiento"
                            value={parseDateOnly(
                                form.data.responsable.fecha_nacimiento,
                            )}
                            onChange={(fecha) =>
                                cambiarResponsable(
                                    'fecha_nacimiento',
                                    toDateOnlyString(fecha),
                                )
                            }
                            maxDate={new Date()}
                            error={Boolean(
                                errors['responsable.fecha_nacimiento'],
                            )}
                            errorMessage={
                                errors['responsable.fecha_nacimiento']
                            }
                        />
                        <EureSelectGroup
                            label="Sexo registral"
                            name="sexo_registral"
                            value={buscarOpcion(
                                OPCIONES_SEXO,
                                form.data.responsable.sexo_registral,
                            )}
                            onChange={(opcion) =>
                                cambiarResponsable(
                                    'sexo_registral',
                                    opcion === null ? '' : String(opcion.value),
                                )
                            }
                            options={OPCIONES_SEXO}
                            placeholder="Buscar opción"
                            error={Boolean(
                                errors['responsable.sexo_registral'],
                            )}
                            errorMessage={errors['responsable.sexo_registral']}
                            isClearable
                        />
                        <EureSelectGroup
                            label="Género"
                            name="genero"
                            value={buscarOpcion(
                                OPCIONES_GENERO,
                                form.data.responsable.genero,
                            )}
                            onChange={(opcion) => {
                                const genero =
                                    opcion === null ? '' : String(opcion.value);
                                form.setData('responsable', {
                                    ...form.data.responsable,
                                    genero,
                                    genero_autodescripcion:
                                        genero === 'Otra identidad'
                                            ? form.data.responsable
                                                  .genero_autodescripcion
                                            : '',
                                });
                            }}
                            options={OPCIONES_GENERO}
                            placeholder="Buscar opción"
                            error={Boolean(errors['responsable.genero'])}
                            errorMessage={errors['responsable.genero']}
                            isClearable
                        />
                        {form.data.responsable.genero === 'Otra identidad' && (
                            <EureInputGroup
                                label="Autodescripción"
                                name="genero_autodescripcion"
                                value={
                                    form.data.responsable.genero_autodescripcion
                                }
                                onChange={(event) =>
                                    cambiarResponsable(
                                        'genero_autodescripcion',
                                        event.target.value,
                                    )
                                }
                                error={Boolean(
                                    errors[
                                        'responsable.genero_autodescripcion'
                                    ],
                                )}
                                errorMessage={
                                    errors['responsable.genero_autodescripcion']
                                }
                                maxLength={100}
                                required
                            />
                        )}
                    </div>
                </EducatioCard>

                <EducatioCard title="Contacto y domicilio" icon={MapPinIcon}>
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <EureInputGroup
                            label="Email"
                            name="email"
                            type="email"
                            value={form.data.responsable.email}
                            onChange={(event) =>
                                cambiarResponsable('email', event.target.value)
                            }
                            error={Boolean(errors['responsable.email'])}
                            errorMessage={errors['responsable.email']}
                            maxLength={254}
                        />
                        <EureTelefonoInputGroup
                            label="Teléfono"
                            name="telefono"
                            value={form.data.responsable.telefono}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'telefono',
                                    event.target.value,
                                )
                            }
                            error={Boolean(errors['responsable.telefono'])}
                            errorMessage={errors['responsable.telefono']}
                            esLineaFija={form.data.responsable.telefono_es_fijo}
                            onEsLineaFijaChange={(fijo) =>
                                cambiarResponsable('telefono_es_fijo', fijo)
                            }
                        />
                        <EureInputGroup
                            label="Domicilio"
                            name="domicilio"
                            value={form.data.responsable.domicilio}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'domicilio',
                                    event.target.value,
                                )
                            }
                            error={Boolean(errors['responsable.domicilio'])}
                            errorMessage={errors['responsable.domicilio']}
                            maxLength={200}
                        />
                        <EureSelectGroup
                            label="Localidad"
                            name="ciudad_id"
                            value={buscarOpcion(
                                opcionesCiudad,
                                form.data.responsable.ciudad_id,
                            )}
                            onChange={(opcion) =>
                                seleccionarCiudad('ciudad_id', 'cpa', opcion)
                            }
                            options={opcionesCiudad}
                            placeholder="Buscar localidad"
                            error={Boolean(errors['responsable.ciudad_id'])}
                            errorMessage={errors['responsable.ciudad_id']}
                            isClearable
                        />
                        <EureInputGroup
                            label="CPA"
                            name="cpa"
                            value={form.data.responsable.cpa}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'cpa',
                                    event.target.value.toLocaleUpperCase(
                                        'es-AR',
                                    ),
                                )
                            }
                            error={Boolean(errors['responsable.cpa'])}
                            errorMessage={errors['responsable.cpa']}
                            maxLength={10}
                        />
                    </div>
                </EducatioCard>

                <EducatioCard
                    title="Datos laborales"
                    icon={BriefcaseBusinessIcon}
                >
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <EureInputGroup
                            label="Profesión u ocupación"
                            name="profesion"
                            value={form.data.responsable.profesion}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'profesion',
                                    event.target.value,
                                )
                            }
                            error={Boolean(errors['responsable.profesion'])}
                            errorMessage={errors['responsable.profesion']}
                            maxLength={100}
                        />
                        <EureInputGroup
                            label="Domicilio laboral"
                            name="domicilio_laboral"
                            value={form.data.responsable.domicilio_laboral}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'domicilio_laboral',
                                    event.target.value,
                                )
                            }
                            error={Boolean(
                                errors['responsable.domicilio_laboral'],
                            )}
                            errorMessage={
                                errors['responsable.domicilio_laboral']
                            }
                            maxLength={200}
                        />
                        <EureSelectGroup
                            label="Localidad laboral"
                            name="ciudad_laboral_id"
                            value={buscarOpcion(
                                opcionesCiudad,
                                form.data.responsable.ciudad_laboral_id,
                            )}
                            onChange={(opcion) =>
                                seleccionarCiudad(
                                    'ciudad_laboral_id',
                                    'cpa_laboral',
                                    opcion,
                                )
                            }
                            options={opcionesCiudad}
                            placeholder="Buscar localidad"
                            error={Boolean(
                                errors['responsable.ciudad_laboral_id'],
                            )}
                            errorMessage={
                                errors['responsable.ciudad_laboral_id']
                            }
                            isClearable
                        />
                        <EureInputGroup
                            label="CPA laboral"
                            name="cpa_laboral"
                            value={form.data.responsable.cpa_laboral}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'cpa_laboral',
                                    event.target.value.toLocaleUpperCase(
                                        'es-AR',
                                    ),
                                )
                            }
                            error={Boolean(errors['responsable.cpa_laboral'])}
                            errorMessage={errors['responsable.cpa_laboral']}
                            maxLength={10}
                        />
                        <EureTelefonoInputGroup
                            label="Teléfono laboral"
                            name="telefono_laboral"
                            value={form.data.responsable.telefono_laboral}
                            onChange={(event) =>
                                cambiarResponsable(
                                    'telefono_laboral',
                                    event.target.value,
                                )
                            }
                            error={Boolean(
                                errors['responsable.telefono_laboral'],
                            )}
                            errorMessage={
                                errors['responsable.telefono_laboral']
                            }
                            esLineaFija={
                                form.data.responsable.telefono_laboral_es_fijo
                            }
                            onEsLineaFijaChange={(fijo) =>
                                cambiarResponsable(
                                    'telefono_laboral_es_fijo',
                                    fijo,
                                )
                            }
                        />
                    </div>
                </EducatioCard>

                <EducatioCard title="Observaciones" icon={NotebookPenIcon}>
                    <EureTextareaGroup
                        label="Observaciones de la persona"
                        name="observaciones"
                        value={form.data.responsable.observaciones}
                        onChange={(event) =>
                            cambiarResponsable(
                                'observaciones',
                                event.target.value,
                            )
                        }
                        error={Boolean(errors['responsable.observaciones'])}
                        errorMessage={errors['responsable.observaciones']}
                        maxLength={5000}
                        rows={4}
                    />
                </EducatioCard>

                {alumno && !responsable && (
                    <EducatioCard
                        title={`Vinculación con ${alumno.apellido}, ${alumno.nombre}`}
                        icon={LinkIcon}
                        description="Definí el parentesco y las funciones que cumple para este alumno."
                    >
                        <FormularioVinculo
                            value={form.data.vinculo}
                            onChange={(vinculo) =>
                                form.setData('vinculo', vinculo)
                            }
                            tiposVinculo={tiposVinculo}
                            errors={errors}
                        />
                    </EducatioCard>
                )}

                <div className="sticky bottom-0 z-10 flex flex-wrap justify-end gap-3 rounded-sm border border-border bg-card/95 px-4 py-3 backdrop-blur">
                    <EureButtonSecondary asChild disabled={form.processing}>
                        <Link href={volver}>
                            <ArrowLeftIcon className="size-4" />
                            Volver
                        </Link>
                    </EureButtonSecondary>
                    <EureButtonPrimary type="submit" loading={form.processing}>
                        <SaveIcon className="size-4" />
                        {responsable ? 'Guardar' : 'Crear y vincular'}
                    </EureButtonPrimary>
                </div>
            </form>
        </>
    );
}

FormularioResponsable.layout = (props: Props) => ({
    breadcrumbs: props.responsable
        ? [
              {
                  title: 'Responsables',
                  href: ResponsableController.index().url,
              },
              {
                  title: `${props.responsable.apellido}, ${props.responsable.nombre}`,
                  href: ResponsableController.show(props.responsable.id).url,
              },
              {
                  title: 'Editar',
                  href: ResponsableController.edit(props.responsable.id).url,
              },
          ]
        : props.alumno
          ? [
                { title: 'Alumnos', href: AlumnoController.index().url },
                {
                    title: `${props.alumno.apellido}, ${props.alumno.nombre}`,
                    href: AlumnoController.show(props.alumno.id, {
                        query: { seccion: 'responsables' },
                    }).url,
                },
                {
                    title: 'Nuevo responsable',
                    href: ResponsableController.create(props.alumno.id).url,
                },
            ]
          : [],
});
