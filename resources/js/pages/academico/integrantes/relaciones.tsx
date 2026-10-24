import {
    BuildingIcon,
    ContactIcon,
    PencilIcon,
    PlusIcon,
    Trash2Icon,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Fragment, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import EducatioCard from '@/components/educatio-card';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import EureConfirmSwal from '@/components/ui/eure/EureConfirmSwal';
import EureInputGroup from '@/components/ui/eure/EureInputGroup';
import EureSelectGroup from '@/components/ui/eure/EureSelectGroup';
import EureTelefonoInputGroup from '@/components/ui/eure/EureTelefonoInputGroup';
import EureTextareaGroup from '@/components/ui/eure/EureTextareaGroup';
import type { ContactoIntegrante, ReferenciaLaboralIntegrante } from './types';

type Campo = {
    nombre: string;
    label: string;
    tipo?: 'telefono' | 'vinculo' | 'observaciones';
    obligatorio?: boolean;
};
type Fila = { id: number | null; clave?: string };
type TablaProps<T extends Fila> = {
    titulo: string;
    icono: LucideIcon;
    singular: string;
    prefijo: string;
    filas: T[];
    campos: Campo[];
    columnas: Array<{ label: string; valor: (fila: T) => ReactNode }>;
    crear: () => T;
    onChange?: (filas: T[], bajaId?: number) => void;
    errores: Record<string, string>;
    vinculos: string[];
    disabled: boolean;
};

function TablaRelacion<T extends Fila>({
    titulo,
    icono,
    singular,
    prefijo,
    filas,
    campos,
    columnas,
    crear,
    onChange,
    errores,
    vinculos,
    disabled,
}: TablaProps<T>) {
    const [edicion, setEdicion] = useState<{
        indice: number | null;
        datos: T;
    } | null>(null);
    const [erroresLocales, setErroresLocales] = useState<
        Record<string, string>
    >({});
    const abrir = (indice: number | null) => {
        setErroresLocales({});
        setEdicion({
            indice,
            datos:
                indice === null
                    ? { ...crear(), clave: crypto.randomUUID() }
                    : { ...filas[indice] },
        });
    };
    const guardar = (event: FormEvent) => {
        event.preventDefault();
        event.stopPropagation();

        if (!edicion || !onChange) {
            return;
        }

        const fallos: Record<string, string> = {};

        for (const campo of campos) {
            if (
                campo.obligatorio &&
                !String(edicion.datos[campo.nombre as keyof T] ?? '').trim()
            ) {
                fallos[campo.nombre] = 'Completá este campo.';
            }
        }

        setErroresLocales(fallos);

        if (Object.keys(fallos).length) {
            return;
        }

        onChange(
            edicion.indice === null
                ? [...filas, edicion.datos]
                : filas.map((fila, indice) =>
                      indice === edicion.indice ? edicion.datos : fila,
                  ),
        );
        setEdicion(null);
    };
    const cambiar = (campo: string, valor: string | boolean) => {
        setEdicion((actual) =>
            actual
                ? { ...actual, datos: { ...actual.datos, [campo]: valor } }
                : actual,
        );
    };

    return (
        <div
            id={prefijo}
            tabIndex={-1}
            className="rounded-sm focus-visible:outline-2 focus-visible:outline-ring"
        >
            <EducatioCard
                title={titulo}
                icon={icono}
                className="break-inside-avoid"
            >
                <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <p className="text-xs text-muted-foreground tabular-nums">
                        {filas.length}{' '}
                        {filas.length === 1 ? 'registro' : 'registros'}
                    </p>
                    {onChange && (
                        <EureButtonSecondary
                            type="button"
                            disabled={disabled}
                            aria-label={`Agregar ${singular}`}
                            onClick={() => abrir(null)}
                        >
                            <PlusIcon aria-hidden="true" />
                            Agregar
                        </EureButtonSecondary>
                    )}
                </div>
                <div className="overflow-x-auto rounded-sm border border-border">
                    <table className="w-full text-left text-sm">
                        <caption className="sr-only">{titulo}</caption>
                        <thead className="bg-muted/50">
                            <tr>
                                {columnas.map((columna) => (
                                    <th
                                        scope="col"
                                        key={columna.label}
                                        className="px-3 py-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                    >
                                        {columna.label}
                                    </th>
                                ))}
                                {onChange && (
                                    <th
                                        scope="col"
                                        className="px-3 py-2 text-right text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                    >
                                        Acciones
                                    </th>
                                )}
                            </tr>
                        </thead>
                        <tbody>
                            {filas.map((fila, indice) => {
                                const erroresFila = Object.entries(
                                    errores,
                                ).filter(([campo]) =>
                                    campo.startsWith(`${prefijo}.${indice}.`),
                                );

                                return (
                                    <Fragment
                                        key={fila.clave ?? fila.id ?? indice}
                                    >
                                        <tr className="border-t align-top">
                                            {columnas.map((columna) => (
                                                <td
                                                    key={columna.label}
                                                    className="max-w-64 min-w-24 px-3 py-2 break-words whitespace-pre-wrap"
                                                >
                                                    {columna.valor(fila) || '—'}
                                                </td>
                                            ))}
                                            {onChange && (
                                                <td className="px-2 py-1 text-right whitespace-nowrap">
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        disabled={disabled}
                                                        aria-label={`Editar ${singular} ${indice + 1}`}
                                                        onClick={() =>
                                                            abrir(indice)
                                                        }
                                                    >
                                                        <PencilIcon className="size-4" />
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        disabled={disabled}
                                                        aria-label={`Quitar ${singular} ${indice + 1}`}
                                                        onClick={async () => {
                                                            if (
                                                                await EureConfirmSwal()(
                                                                    {
                                                                        title: `Quitar ${singular}`,
                                                                        text: 'Se quitará al guardar la ficha.',
                                                                        confirmText:
                                                                            'Quitar',
                                                                    },
                                                                )
                                                            ) {
                                                                onChange(
                                                                    filas.filter(
                                                                        (
                                                                            _,
                                                                            posicion,
                                                                        ) =>
                                                                            posicion !==
                                                                            indice,
                                                                    ),
                                                                    fila.id ??
                                                                        undefined,
                                                                );
                                                            }
                                                        }}
                                                    >
                                                        <Trash2Icon className="size-4 text-eure-error" />
                                                    </Button>
                                                </td>
                                            )}
                                        </tr>
                                        {erroresFila.length > 0 && (
                                            <tr>
                                                <td
                                                    colSpan={
                                                        columnas.length +
                                                        (onChange ? 1 : 0)
                                                    }
                                                    className="px-3 pb-2 text-sm text-eure-error"
                                                    role="alert"
                                                >
                                                    {erroresFila
                                                        .map(
                                                            ([, mensaje]) =>
                                                                mensaje,
                                                        )
                                                        .join(' ')}
                                                </td>
                                            </tr>
                                        )}
                                    </Fragment>
                                );
                            })}
                            {!filas.length && (
                                <tr>
                                    <td
                                        colSpan={
                                            columnas.length + (onChange ? 1 : 0)
                                        }
                                        className="p-4 text-muted-foreground"
                                    >
                                        Sin registros.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {Object.entries(errores)
                    .filter(
                        ([campo]) =>
                            campo === prefijo ||
                            campo.startsWith(`${prefijo}_bajas`),
                    )
                    .map(([campo, mensaje]) => (
                        <p
                            key={campo}
                            role="alert"
                            className="text-sm text-eure-error"
                        >
                            {mensaje}
                        </p>
                    ))}
                {edicion && (
                    <Dialog
                        open
                        onOpenChange={(abierto) => {
                            if (!abierto) {
                                setEdicion(null);
                            }
                        }}
                    >
                        <DialogContent className="max-h-[90dvh] overflow-y-auto sm:max-w-3xl">
                            <DialogHeader>
                                <DialogTitle>
                                    {edicion.indice === null
                                        ? 'Agregar'
                                        : 'Editar'}{' '}
                                    {singular}
                                </DialogTitle>
                                <DialogDescription>
                                    Los cambios se aplicarán al guardar la ficha
                                    del integrante.
                                </DialogDescription>
                            </DialogHeader>
                            <form
                                onSubmit={guardar}
                                noValidate
                                className="space-y-5"
                            >
                                <div className="grid items-start gap-4 sm:grid-cols-2">
                                    {campos.map((campo, posicion) => {
                                        const valor = String(
                                            edicion.datos[
                                                campo.nombre as keyof T
                                            ] ?? '',
                                        );
                                        const error =
                                            erroresLocales[campo.nombre] ??
                                            (edicion.indice !== null
                                                ? errores[
                                                      `${prefijo}.${edicion.indice}.${campo.nombre}`
                                                  ]
                                                : undefined);
                                        const props = {
                                            name: `${prefijo}-${campo.nombre}`,
                                            label: campo.label,
                                            error: Boolean(error),
                                            errorMessage: error,
                                        };

                                        if (campo.tipo === 'telefono') {
                                            return (
                                                <EureTelefonoInputGroup
                                                    key={campo.nombre}
                                                    {...props}
                                                    value={valor}
                                                    onChange={(event) =>
                                                        cambiar(
                                                            campo.nombre,
                                                            event.target.value,
                                                        )
                                                    }
                                                    esLineaFija={Boolean(
                                                        edicion.datos[
                                                            `${campo.nombre}_es_fijo` as keyof T
                                                        ],
                                                    )}
                                                    onEsLineaFijaChange={(
                                                        fijo,
                                                    ) =>
                                                        cambiar(
                                                            `${campo.nombre}_es_fijo`,
                                                            fijo,
                                                        )
                                                    }
                                                />
                                            );
                                        }

                                        if (campo.tipo === 'vinculo') {
                                            return (
                                                <EureSelectGroup
                                                    key={campo.nombre}
                                                    {...props}
                                                    options={vinculos.map(
                                                        (vinculo) => ({
                                                            value: vinculo,
                                                            label: vinculo,
                                                        }),
                                                    )}
                                                    value={
                                                        valor
                                                            ? {
                                                                  value: valor,
                                                                  label: valor,
                                                              }
                                                            : null
                                                    }
                                                    onChange={(opcion) =>
                                                        cambiar(
                                                            campo.nombre,
                                                            String(
                                                                opcion?.value ??
                                                                    '',
                                                            ),
                                                        )
                                                    }
                                                    isClearable
                                                />
                                            );
                                        }

                                        if (campo.tipo === 'observaciones') {
                                            return (
                                                <EureTextareaGroup
                                                    key={campo.nombre}
                                                    {...props}
                                                    className="sm:col-span-2"
                                                    value={valor}
                                                    onChange={(event) =>
                                                        cambiar(
                                                            campo.nombre,
                                                            event.target.value,
                                                        )
                                                    }
                                                    maxLength={2000}
                                                />
                                            );
                                        }

                                        return (
                                            <EureInputGroup
                                                key={campo.nombre}
                                                {...props}
                                                value={valor}
                                                onChange={(event) =>
                                                    cambiar(
                                                        campo.nombre,
                                                        event.target.value,
                                                    )
                                                }
                                                required={campo.obligatorio}
                                                maxLength={150}
                                                className={
                                                    campo.nombre === 'tiempo'
                                                        ? 'sm:col-span-2 sm:max-w-60'
                                                        : undefined
                                                }
                                                autoFocus={posicion === 0}
                                            />
                                        );
                                    })}
                                </div>
                                <DialogFooter>
                                    <EureButtonSecondary
                                        type="button"
                                        onClick={() => setEdicion(null)}
                                    >
                                        Cancelar
                                    </EureButtonSecondary>
                                    <EureButtonPrimary type="submit">
                                        Aceptar
                                    </EureButtonPrimary>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                )}
            </EducatioCard>
        </div>
    );
}

const CAMPOS_REFERENCIA: Campo[] = [
    { nombre: 'lugar', label: 'Lugar', obligatorio: true },
    { nombre: 'cargo', label: 'Cargo' },
    { nombre: 'tiempo', label: 'Tiempo' },
    { nombre: 'referencia_1_nombre', label: 'Referencia 1: nombre' },
    {
        nombre: 'referencia_1_telefono',
        label: 'Referencia 1: teléfono',
        tipo: 'telefono',
    },
    { nombre: 'referencia_2_nombre', label: 'Referencia 2: nombre' },
    {
        nombre: 'referencia_2_telefono',
        label: 'Referencia 2: teléfono',
        tipo: 'telefono',
    },
    { nombre: 'observaciones', label: 'Observaciones', tipo: 'observaciones' },
];
const CAMPOS_CONTACTO: Campo[] = [
    { nombre: 'nombre', label: 'Nombre y apellido', obligatorio: true },
    { nombre: 'telefono', label: 'Teléfono', tipo: 'telefono' },
    { nombre: 'vinculo', label: 'Vínculo', tipo: 'vinculo' },
    { nombre: 'observaciones', label: 'Observaciones', tipo: 'observaciones' },
];

type Props = {
    referencias: ReferenciaLaboralIntegrante[];
    contactos: ContactoIntegrante[];
    onReferenciasChange?: (
        filas: ReferenciaLaboralIntegrante[],
        bajaId?: number,
    ) => void;
    onContactosChange?: (filas: ContactoIntegrante[], bajaId?: number) => void;
    vinculos?: string[];
    errores?: Record<string, string>;
    disabled?: boolean;
};

export default function RelacionesIntegrante({
    referencias,
    contactos,
    onReferenciasChange,
    onContactosChange,
    vinculos = [],
    errores = {},
    disabled = false,
}: Props) {
    return (
        <div className="flex flex-col gap-4">
            <TablaRelacion<ReferenciaLaboralIntegrante>
                titulo="Referencias laborales"
                icono={BuildingIcon}
                singular="referencia laboral"
                prefijo="referencias_laborales"
                filas={referencias}
                campos={CAMPOS_REFERENCIA}
                crear={() => ({
                    id: null,
                    lugar: '',
                    cargo: '',
                    tiempo: '',
                    referencia_1_nombre: '',
                    referencia_1_telefono: '',
                    referencia_1_telefono_es_fijo: false,
                    referencia_2_nombre: '',
                    referencia_2_telefono: '',
                    referencia_2_telefono_es_fijo: false,
                    observaciones: '',
                })}
                columnas={[
                    { label: 'Lugar', valor: (fila) => fila.lugar },
                    { label: 'Cargo', valor: (fila) => fila.cargo },
                    { label: 'Tiempo', valor: (fila) => fila.tiempo },
                    {
                        label: 'Referencia 1',
                        valor: (fila) =>
                            [
                                fila.referencia_1_nombre,
                                fila.referencia_1_telefono,
                            ]
                                .filter(Boolean)
                                .join('\n'),
                    },
                    {
                        label: 'Referencia 2',
                        valor: (fila) =>
                            [
                                fila.referencia_2_nombre,
                                fila.referencia_2_telefono,
                            ]
                                .filter(Boolean)
                                .join('\n'),
                    },
                    {
                        label: 'Observaciones',
                        valor: (fila) => fila.observaciones,
                    },
                ]}
                onChange={onReferenciasChange}
                errores={errores}
                vinculos={vinculos}
                disabled={disabled}
            />
            <TablaRelacion<ContactoIntegrante>
                titulo="Contactos"
                icono={ContactIcon}
                singular="contacto"
                prefijo="contactos"
                filas={contactos}
                campos={CAMPOS_CONTACTO}
                crear={() => ({
                    id: null,
                    nombre: '',
                    telefono: '',
                    telefono_es_fijo: false,
                    vinculo: '',
                    observaciones: '',
                })}
                columnas={[
                    {
                        label: 'Nombre y apellido',
                        valor: (fila) => fila.nombre,
                    },
                    { label: 'Teléfono', valor: (fila) => fila.telefono },
                    { label: 'Vínculo', valor: (fila) => fila.vinculo },
                    {
                        label: 'Observaciones',
                        valor: (fila) => fila.observaciones,
                    },
                ]}
                onChange={onContactosChange}
                errores={errores}
                vinculos={vinculos}
                disabled={disabled}
            />
        </div>
    );
}
