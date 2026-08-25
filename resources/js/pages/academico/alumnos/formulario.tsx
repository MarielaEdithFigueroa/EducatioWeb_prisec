import type { UrlMethodPair } from '@inertiajs/core';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, SaveIcon } from 'lucide-react';
import { useMemo } from 'react';
import AlumnoController from '@/actions/App/Http/Controllers/Academico/AlumnoController';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureDatePickerGroup from '@/components/ui/eure/EureDatePickerGroup';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import type { EureSelectOption } from '@/components/ui/eure/EureSelectGroup';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import { parseDateOnly, toDateOnlyString } from '@/lib/date';

type TipoDocumento = {
    id: number;
    nombre: string;
};

type Provincia = {
    id: number;
    activo: boolean;
    nombre: string;
};

type Ciudad = {
    id: number;
    activo: boolean;
    provincia_id: number;
    nombre: string;
    codigo_postal: string | null;
    provincia: Provincia;
};

type Nacionalidad = {
    id: number;
    activo: boolean;
    nombre: string;
};

type GrupoSanguineo = {
    id: number;
    codigo: string;
    nombre: string;
};

type Alumno = {
    id: number;
    activo: boolean;
    legajo: string;
    apellido: string;
    nombre: string;
    nombre_elegido: string | null;
    tipo_documento_id: number | null;
    numero_documento: string | null;
    cuilt: string | null;
    fecha_nacimiento: string | null;
    ciudad_nacimiento_id: number | null;
    nacionalidad_id: number | null;
    grupo_sanguineo_id: number | null;
    sexo_registral: string | null;
    genero: string | null;
    genero_autodescripcion: string | null;
    email: string | null;
    domicilio: string | null;
    ciudad_id: number | null;
    cpa: string | null;
    fecha_ingreso: string | null;
    fecha_inicio_cursado: string | null;
    libro: string | null;
    folio: string | null;
    autoriza_uso_imagen: boolean | null;
    observaciones: string | null;
};

type FormularioAlumno = {
    legajo: string;
    apellido: string;
    nombre: string;
    nombre_elegido: string;
    tipo_documento_id: number | '';
    numero_documento: string;
    cuilt: string;
    fecha_nacimiento: string;
    ciudad_nacimiento_id: number | '';
    nacionalidad_id: number | '';
    grupo_sanguineo_id: number | '';
    sexo_registral: string;
    genero: string;
    genero_autodescripcion: string;
    email: string;
    domicilio: string;
    ciudad_id: number | '';
    cpa: string;
    fecha_ingreso: string;
    fecha_inicio_cursado: string;
    libro: string;
    folio: string;
    autoriza_uso_imagen: boolean | null;
    observaciones: string;
};

type Props = {
    alumno: Alumno | null;
    tiposDocumento: TipoDocumento[];
    ciudades: Ciudad[];
    nacionalidades: Nacionalidad[];
    gruposSanguineos: GrupoSanguineo[];
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

const OPCIONES_IMAGEN: EureSelectOption[] = [
    { value: 'sin_informar', label: 'Sin informar' },
    { value: 1, label: 'Sí' },
    { value: 0, label: 'No' },
];

function buscarOpcion(
    opciones: EureSelectOption[],
    valor: string | number | '',
): EureSelectOption | null {
    return opciones.find((opcion) => opcion.value === valor) ?? null;
}

export default function FormularioAlumno({
    alumno,
    tiposDocumento,
    ciudades,
    nacionalidades,
    gruposSanguineos,
}: Props) {
    const form = useForm<FormularioAlumno>({
        legajo: alumno?.legajo ?? '',
        apellido: alumno?.apellido ?? '',
        nombre: alumno?.nombre ?? '',
        nombre_elegido: alumno?.nombre_elegido ?? '',
        tipo_documento_id: alumno?.tipo_documento_id ?? '',
        numero_documento: alumno?.numero_documento ?? '',
        cuilt: alumno?.cuilt ?? '',
        fecha_nacimiento: alumno?.fecha_nacimiento ?? '',
        ciudad_nacimiento_id: alumno?.ciudad_nacimiento_id ?? '',
        nacionalidad_id: alumno?.nacionalidad_id ?? '',
        grupo_sanguineo_id: alumno?.grupo_sanguineo_id ?? '',
        sexo_registral: alumno?.sexo_registral ?? '',
        genero: alumno?.genero ?? '',
        genero_autodescripcion: alumno?.genero_autodescripcion ?? '',
        email: alumno?.email ?? '',
        domicilio: alumno?.domicilio ?? '',
        ciudad_id: alumno?.ciudad_id ?? '',
        cpa: alumno?.cpa ?? '',
        fecha_ingreso: alumno?.fecha_ingreso ?? '',
        fecha_inicio_cursado: alumno?.fecha_inicio_cursado ?? '',
        libro: alumno?.libro ?? '',
        folio: alumno?.folio ?? '',
        autoriza_uso_imagen: alumno?.autoriza_uso_imagen ?? null,
        observaciones: alumno?.observaciones ?? '',
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

    const opcionesNacionalidad = useMemo<EureSelectOption[]>(
        () =>
            nacionalidades.map((nacionalidad) => ({
                value: nacionalidad.id,
                label: `${nacionalidad.nombre}${nacionalidad.activo ? '' : ' (inactiva)'}`,
            })),
        [nacionalidades],
    );

    const opcionesGrupoSanguineo = useMemo<EureSelectOption[]>(
        () =>
            gruposSanguineos.map((grupo) => ({
                value: grupo.id,
                label: `${grupo.codigo} · ${grupo.nombre}`,
            })),
        [gruposSanguineos],
    );

    const opcionImagen =
        alumno !== null || form.data.autoriza_uso_imagen !== null
            ? (OPCIONES_IMAGEN.find((opcion) => {
                  if (opcion.value === 'sin_informar') {
                      return form.data.autoriza_uso_imagen === null;
                  }

                  return (
                      Boolean(opcion.value) === form.data.autoriza_uso_imagen
                  );
              }) ?? null)
            : OPCIONES_IMAGEN[0];

    const guardar = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const destino: UrlMethodPair = alumno
            ? AlumnoController.update(alumno.id)
            : AlumnoController.store();

        form.submit(destino, {
            preserveScroll: true,
        });
    };

    const seleccionarCiudadDomicilio = (opcion: EureSelectOption | null) => {
        const ciudadId = opcion === null ? '' : Number(opcion.value);
        form.setData('ciudad_id', ciudadId);

        if (form.data.cpa === '' && ciudadId !== '') {
            const codigoPostal = ciudades.find(
                (ciudad) => ciudad.id === ciudadId,
            )?.codigo_postal;

            if (codigoPostal) {
                form.setData('cpa', codigoPostal);
            }
        }
    };

    return (
        <>
            <Head title={alumno ? 'Editar alumno' : 'Nuevo alumno'} />

            <form
                onSubmit={guardar}
                className="flex flex-1 flex-col gap-6 p-4 pb-0 md:p-6 md:pb-0"
            >
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                        {alumno ? 'Editar alumno' : 'Nuevo alumno'}
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Ficha personal y datos del legajo.
                    </p>
                </div>

                {!alumno?.activo && (
                    <div className="rounded-md border border-eure-error/30 bg-eure-error/10 px-4 py-3 text-sm text-eure-error">
                        Este alumno está inactivo. Podés actualizar sus datos o
                        reactivarlo desde el listado.
                    </div>
                )}

                <section className="space-y-4 border-b border-border pb-6">
                    <div>
                        <h2 className="text-lg font-semibold text-foreground">
                            Identificación
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Legajo, nombres y documentación personal.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <EureInputGroup
                            label="Legajo"
                            name="legajo"
                            value={form.data.legajo}
                            onChange={(event) =>
                                form.setData('legajo', event.target.value)
                            }
                            error={Boolean(form.errors.legajo)}
                            errorMessage={form.errors.legajo}
                            maxLength={30}
                            autoFocus
                            required
                        />
                        <EureInputGroup
                            label="Apellido"
                            name="apellido"
                            value={form.data.apellido}
                            onChange={(event) =>
                                form.setData('apellido', event.target.value)
                            }
                            error={Boolean(form.errors.apellido)}
                            errorMessage={form.errors.apellido}
                            maxLength={100}
                            required
                        />
                        <EureInputGroup
                            label="Nombre"
                            name="nombre"
                            value={form.data.nombre}
                            onChange={(event) =>
                                form.setData('nombre', event.target.value)
                            }
                            error={Boolean(form.errors.nombre)}
                            errorMessage={form.errors.nombre}
                            maxLength={100}
                            required
                        />
                        <EureInputGroup
                            label="Nombre elegido"
                            name="nombre_elegido"
                            value={form.data.nombre_elegido}
                            onChange={(event) =>
                                form.setData(
                                    'nombre_elegido',
                                    event.target.value,
                                )
                            }
                            error={Boolean(form.errors.nombre_elegido)}
                            errorMessage={form.errors.nombre_elegido}
                            maxLength={100}
                        />

                        <EureSelectGroup
                            label="Tipo de documento"
                            name="tipo_documento_id"
                            value={buscarOpcion(
                                opcionesTipoDocumento,
                                form.data.tipo_documento_id,
                            )}
                            onChange={(opcion) =>
                                form.setData(
                                    'tipo_documento_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesTipoDocumento}
                            placeholder="Buscar tipo"
                            error={Boolean(form.errors.tipo_documento_id)}
                            errorMessage={form.errors.tipo_documento_id}
                            isClearable
                        />
                        <EureInputGroup
                            label="Número de documento"
                            name="numero_documento"
                            value={form.data.numero_documento}
                            onChange={(event) =>
                                form.setData(
                                    'numero_documento',
                                    event.target.value.toLocaleUpperCase(
                                        'es-AR',
                                    ),
                                )
                            }
                            error={Boolean(form.errors.numero_documento)}
                            errorMessage={form.errors.numero_documento}
                            maxLength={20}
                        />
                        <EureInputGroup
                            label="CUIL/T"
                            name="cuilt"
                            value={form.data.cuilt}
                            onChange={(event) =>
                                form.setData('cuilt', event.target.value)
                            }
                            error={Boolean(form.errors.cuilt)}
                            errorMessage={form.errors.cuilt}
                            mask="__-________-_"
                            replacement={{ _: /\d/ }}
                        />
                        <EureInputGroup
                            label="Email"
                            name="email"
                            type="email"
                            value={form.data.email}
                            onChange={(event) =>
                                form.setData('email', event.target.value)
                            }
                            error={Boolean(form.errors.email)}
                            errorMessage={form.errors.email}
                            maxLength={254}
                        />
                    </div>
                </section>

                <section className="space-y-4 border-b border-border pb-6">
                    <div>
                        <h2 className="text-lg font-semibold text-foreground">
                            Nacimiento e identidad
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Datos registrales y personales informados.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <EureDatePickerGroup
                            label="Fecha de nacimiento"
                            name="fecha_nacimiento"
                            value={parseDateOnly(form.data.fecha_nacimiento)}
                            onChange={(fecha) =>
                                form.setData(
                                    'fecha_nacimiento',
                                    toDateOnlyString(fecha),
                                )
                            }
                            maxDate={new Date()}
                            error={Boolean(form.errors.fecha_nacimiento)}
                            errorMessage={form.errors.fecha_nacimiento}
                        />
                        <EureSelectGroup
                            label="Localidad de nacimiento"
                            name="ciudad_nacimiento_id"
                            value={buscarOpcion(
                                opcionesCiudad,
                                form.data.ciudad_nacimiento_id,
                            )}
                            onChange={(opcion) =>
                                form.setData(
                                    'ciudad_nacimiento_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesCiudad}
                            placeholder="Buscar localidad"
                            error={Boolean(form.errors.ciudad_nacimiento_id)}
                            errorMessage={form.errors.ciudad_nacimiento_id}
                            isClearable
                        />
                        <EureSelectGroup
                            label="Nacionalidad"
                            name="nacionalidad_id"
                            value={buscarOpcion(
                                opcionesNacionalidad,
                                form.data.nacionalidad_id,
                            )}
                            onChange={(opcion) =>
                                form.setData(
                                    'nacionalidad_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesNacionalidad}
                            placeholder="Buscar nacionalidad"
                            error={Boolean(form.errors.nacionalidad_id)}
                            errorMessage={form.errors.nacionalidad_id}
                            isClearable
                        />
                        <EureSelectGroup
                            label="Grupo sanguíneo"
                            name="grupo_sanguineo_id"
                            value={buscarOpcion(
                                opcionesGrupoSanguineo,
                                form.data.grupo_sanguineo_id,
                            )}
                            onChange={(opcion) =>
                                form.setData(
                                    'grupo_sanguineo_id',
                                    opcion === null ? '' : Number(opcion.value),
                                )
                            }
                            options={opcionesGrupoSanguineo}
                            placeholder="Buscar grupo"
                            error={Boolean(form.errors.grupo_sanguineo_id)}
                            errorMessage={form.errors.grupo_sanguineo_id}
                            isClearable
                        />
                        <EureSelectGroup
                            label="Sexo registral"
                            name="sexo_registral"
                            value={buscarOpcion(
                                OPCIONES_SEXO,
                                form.data.sexo_registral,
                            )}
                            onChange={(opcion) =>
                                form.setData(
                                    'sexo_registral',
                                    opcion === null ? '' : String(opcion.value),
                                )
                            }
                            options={OPCIONES_SEXO}
                            placeholder="Buscar opción"
                            error={Boolean(form.errors.sexo_registral)}
                            errorMessage={form.errors.sexo_registral}
                            isClearable
                        />
                        <EureSelectGroup
                            label="Género"
                            name="genero"
                            value={buscarOpcion(
                                OPCIONES_GENERO,
                                form.data.genero,
                            )}
                            onChange={(opcion) => {
                                const genero =
                                    opcion === null ? '' : String(opcion.value);
                                form.setData('genero', genero);

                                if (genero !== 'Otra identidad') {
                                    form.setData('genero_autodescripcion', '');
                                }
                            }}
                            options={OPCIONES_GENERO}
                            placeholder="Buscar opción"
                            error={Boolean(form.errors.genero)}
                            errorMessage={form.errors.genero}
                            isClearable
                        />
                        {form.data.genero === 'Otra identidad' && (
                            <EureInputGroup
                                label="Autodescripción de género"
                                name="genero_autodescripcion"
                                value={form.data.genero_autodescripcion}
                                onChange={(event) =>
                                    form.setData(
                                        'genero_autodescripcion',
                                        event.target.value,
                                    )
                                }
                                error={Boolean(
                                    form.errors.genero_autodescripcion,
                                )}
                                errorMessage={
                                    form.errors.genero_autodescripcion
                                }
                                maxLength={100}
                                required
                            />
                        )}
                    </div>
                </section>

                <section className="space-y-4 border-b border-border pb-6">
                    <div>
                        <h2 className="text-lg font-semibold text-foreground">
                            Domicilio
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Dirección declarada y localidad de residencia.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <EureInputGroup
                            label="Domicilio"
                            name="domicilio"
                            value={form.data.domicilio}
                            onChange={(event) =>
                                form.setData('domicilio', event.target.value)
                            }
                            error={Boolean(form.errors.domicilio)}
                            errorMessage={form.errors.domicilio}
                            maxLength={200}
                            classNameWrapper="md:col-span-2"
                        />
                        <EureSelectGroup
                            label="Localidad"
                            name="ciudad_id"
                            value={buscarOpcion(
                                opcionesCiudad,
                                form.data.ciudad_id,
                            )}
                            onChange={seleccionarCiudadDomicilio}
                            options={opcionesCiudad}
                            placeholder="Buscar localidad"
                            error={Boolean(form.errors.ciudad_id)}
                            errorMessage={form.errors.ciudad_id}
                            isClearable
                        />
                        <EureInputGroup
                            label="Código postal"
                            name="cpa"
                            value={form.data.cpa}
                            onChange={(event) =>
                                form.setData(
                                    'cpa',
                                    event.target.value.toLocaleUpperCase(
                                        'es-AR',
                                    ),
                                )
                            }
                            error={Boolean(form.errors.cpa)}
                            errorMessage={form.errors.cpa}
                            maxLength={10}
                        />
                    </div>
                </section>

                <section className="space-y-4 border-b border-border pb-6">
                    <div>
                        <h2 className="text-lg font-semibold text-foreground">
                            Datos institucionales
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Fechas y referencias internas del legajo.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <EureDatePickerGroup
                            label="Fecha de ingreso"
                            name="fecha_ingreso"
                            value={parseDateOnly(form.data.fecha_ingreso)}
                            onChange={(fecha) =>
                                form.setData(
                                    'fecha_ingreso',
                                    toDateOnlyString(fecha),
                                )
                            }
                            error={Boolean(form.errors.fecha_ingreso)}
                            errorMessage={form.errors.fecha_ingreso}
                        />
                        <EureDatePickerGroup
                            label="Inicio de cursado"
                            name="fecha_inicio_cursado"
                            value={parseDateOnly(
                                form.data.fecha_inicio_cursado,
                            )}
                            onChange={(fecha) =>
                                form.setData(
                                    'fecha_inicio_cursado',
                                    toDateOnlyString(fecha),
                                )
                            }
                            error={Boolean(form.errors.fecha_inicio_cursado)}
                            errorMessage={form.errors.fecha_inicio_cursado}
                        />
                        <EureInputGroup
                            label="Libro"
                            name="libro"
                            value={form.data.libro}
                            onChange={(event) =>
                                form.setData('libro', event.target.value)
                            }
                            error={Boolean(form.errors.libro)}
                            errorMessage={form.errors.libro}
                            maxLength={30}
                        />
                        <EureInputGroup
                            label="Folio"
                            name="folio"
                            value={form.data.folio}
                            onChange={(event) =>
                                form.setData('folio', event.target.value)
                            }
                            error={Boolean(form.errors.folio)}
                            errorMessage={form.errors.folio}
                            maxLength={30}
                        />
                        <EureSelectGroup
                            label="Autoriza uso de imagen"
                            name="autoriza_uso_imagen"
                            value={opcionImagen}
                            onChange={(opcion) => {
                                const valor = opcion?.value;
                                form.setData(
                                    'autoriza_uso_imagen',
                                    valor === 1
                                        ? true
                                        : valor === 0
                                          ? false
                                          : null,
                                );
                            }}
                            options={OPCIONES_IMAGEN}
                            placeholder="Buscar opción"
                            error={Boolean(form.errors.autoriza_uso_imagen)}
                            errorMessage={form.errors.autoriza_uso_imagen}
                        />
                    </div>
                </section>

                <section className="space-y-4 pb-2">
                    <div>
                        <h2 className="text-lg font-semibold text-foreground">
                            Observaciones
                        </h2>
                    </div>

                    <EureTextareaGroup
                        label="Observaciones del legajo"
                        name="observaciones"
                        value={form.data.observaciones}
                        onChange={(event) =>
                            form.setData('observaciones', event.target.value)
                        }
                        error={Boolean(form.errors.observaciones)}
                        errorMessage={form.errors.observaciones}
                        maxLength={5000}
                        rows={4}
                    />
                </section>

                <div className="sticky bottom-0 z-10 -mx-4 flex justify-end gap-3 border-t border-border bg-background/95 px-4 py-4 backdrop-blur md:-mx-6 md:px-6">
                    <EureButtonSecondary asChild disabled={form.processing}>
                        <Link href={AlumnoController.index()}>
                            <ArrowLeftIcon className="size-4" />
                            Volver
                        </Link>
                    </EureButtonSecondary>
                    <EureButtonPrimary type="submit" loading={form.processing}>
                        <SaveIcon className="size-4" />
                        Guardar
                    </EureButtonPrimary>
                </div>
            </form>
        </>
    );
}

FormularioAlumno.layout = (props: Props) => ({
    breadcrumbs: [
        {
            title: 'Alumnos',
            href: AlumnoController.index().url,
        },
        {
            title: props.alumno ? 'Editar alumno' : 'Nuevo alumno',
            href: props.alumno
                ? AlumnoController.edit(props.alumno.id).url
                : AlumnoController.create().url,
        },
    ],
});
