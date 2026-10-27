import { Head, Link, router } from '@inertiajs/react';
import {
    FileTextIcon,
    MoreVerticalIcon,
    PencilIcon,
    PrinterIcon,
    RotateCcwIcon,
    Trash2Icon,
} from 'lucide-react';
import { useState } from 'react';
import EducatioCard from '@/components/educatio-card';
import FichaRapidaPersona from '@/components/ficha-rapida-persona';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { formatCuilt } from '@/lib/cuit';
import { formatDate, yearsSince } from '@/lib/date';
import { edit, foto, imprimir, index } from '@/routes/academico/integrantes';
import type { Adjunto, ConfigAdjuntos } from '@/types/adjuntos';
import AdjuntosIntegrante from './adjuntos';
import ChecklistIntegrante, { contarPresentados } from './checklist';
import PestanasIntegrante from './pestanas';
import RelacionesIntegrante from './relaciones';
import { contextoIntegrante, GRUPOS_CAMPOS, SECCIONES } from './types';
import type {
    CampoIntegrante,
    DocumentoIntegrante,
    FiltrosIntegrante,
    GrupoCampos,
    Integrante,
    SeccionIntegrante,
} from './types';
import { useEstadoIntegrante } from './use-estado-integrante';

export type FichaProps = {
    integrante: Integrante;
    documentacion: DocumentoIntegrante[];
    adjuntosLibres: Adjunto[];
    configAdjuntos: ConfigAdjuntos;
    filters: FiltrosIntegrante;
    seccion: SeccionIntegrante;
    configPdf?: ConfigAdjuntos;
};

const VACIO = '—';

/** Columnas internas de cada grupo. Literales para que Tailwind las detecte. */
const COLUMNAS: Record<GrupoCampos['columnas'], string> = {
    2: 'sm:grid-cols-2',
    3: 'sm:grid-cols-2 lg:grid-cols-3',
    4: 'sm:grid-cols-2 lg:grid-cols-4',
    5: 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
};

function valorCampo(integrante: Integrante, campo: CampoIntegrante): string {
    const valor = integrante[campo.campo];

    switch (campo.campo) {
        case 'fecha_nacimiento': {
            const fecha = formatDate(String(valor ?? ''), VACIO);
            const edad = yearsSince(String(valor ?? ''));

            return edad === null ? fecha : `${fecha} · ${edad} años`;
        }
        case 'cuilt':
            return formatCuilt(String(valor ?? '')) || VACIO;
        case 'tipo_documento_id':
            return integrante.tipo_documento?.nombre ?? VACIO;
        case 'ciudad_id':
            return integrante.ciudad
                ? `${integrante.ciudad.nombre} · ${integrante.ciudad.provincia.nombre}`
                : VACIO;
        case 'ciudad_nacimiento_id':
            return integrante.ciudad_nacimiento
                ? `${integrante.ciudad_nacimiento.nombre} · ${integrante.ciudad_nacimiento.provincia.nombre}`
                : VACIO;
        case 'nacionalidad_id':
            return integrante.nacionalidad?.nombre ?? VACIO;
        case 'sexo_registral':
            return (
                { F: 'Femenino', M: 'Masculino', X: 'X' }[String(valor)] ??
                VACIO
            );
        default:
            break;
    }

    if (campo.tipo === 'fecha') {
        return formatDate(String(valor ?? ''), VACIO);
    }

    return String(valor ?? '') || VACIO;
}

/** Valores que no son columnas de la tabla pero pertenecen a un grupo. */
function derivados(
    integrante: Integrante,
    grupo: GrupoCampos,
): Array<{ label: string; valor: string }> {
    if (grupo.titulo === 'Información profesional') {
        return [
            {
                label: 'Tiene aportes',
                valor: integrante.tiene_aportes ? 'Sí' : 'No',
            },
        ];
    }

    return [];
}

function CampoFicha({
    label,
    valor,
    full = false,
}: {
    label: string;
    valor: string;
    full?: boolean;
}) {
    return (
        <div className={`min-w-0 ${full ? 'sm:col-span-full' : ''}`}>
            <dt className="text-[10.5px] font-semibold tracking-wider text-muted-foreground uppercase">
                {label}
            </dt>
            <dd
                className={`mt-0.5 text-sm break-words whitespace-pre-wrap tabular-nums ${
                    valor === VACIO
                        ? 'text-muted-foreground'
                        : 'font-medium text-foreground'
                }`}
            >
                {valor}
            </dd>
        </div>
    );
}

export function ContenidoFichaIntegrante({
    integrante,
    documentacion,
    seccion,
    configPdf,
    adjuntosLibres = [],
    configAdjuntos,
}: Pick<FichaProps, 'integrante' | 'documentacion'> & {
    seccion?: SeccionIntegrante;
    adjuntosLibres?: Adjunto[];
    configAdjuntos?: ConfigAdjuntos;
    configPdf?: ConfigAdjuntos;
}) {
    const visibles = SECCIONES.filter((item) =>
        seccion
            ? item.value === seccion
            : // Sin pestañas (impresión): se omite lo que no tiene nada que mostrar.
              item.value !== 'adjuntos' || Boolean(configAdjuntos),
    );

    return (
        <div className="flex flex-col gap-4">
            {visibles.map((item) => {
                const grupos = GRUPOS_CAMPOS.filter(
                    (grupo) => grupo.seccion === item.value,
                ).map((grupo) => {
                    const campos = [
                        ...grupo.campos.map((campo) => ({
                            label: campo.label,
                            valor: valorCampo(integrante, campo),
                            full: campo.ancho === 'full',
                        })),
                        ...derivados(integrante, grupo).map((extra) => ({
                            ...extra,
                            full: false,
                        })),
                    ];

                    return {
                        grupo,
                        campos,
                    };
                });

                return (
                    <div
                        key={item.value}
                        id={`panel-integrante-${item.value}`}
                        role={seccion ? 'tabpanel' : undefined}
                        aria-labelledby={
                            seccion ? `tab-integrante-${item.value}` : undefined
                        }
                        className="flex flex-col gap-4"
                    >
                        {grupos.length > 0 && (
                            <div className="grid items-start gap-4 lg:grid-cols-2">
                                {grupos.map(({ grupo, campos }) => {
                                    return (
                                        <EducatioCard
                                            key={grupo.titulo}
                                            title={grupo.titulo}
                                            icon={grupo.icono}
                                            className={`break-inside-avoid ${
                                                grupo.ancho === 'completa'
                                                    ? 'lg:col-span-2'
                                                    : ''
                                            }`}
                                        >
                                            <dl
                                                className={`grid gap-x-5 gap-y-3 ${COLUMNAS[grupo.columnas]}`}
                                            >
                                                {campos.map((campo) => (
                                                    <CampoFicha
                                                        key={campo.label}
                                                        {...campo}
                                                    />
                                                ))}
                                            </dl>
                                        </EducatioCard>
                                    );
                                })}
                            </div>
                        )}

                        {item.value === 'documentacion' && (
                            <EducatioCard
                                title="Documentación del legajo"
                                icon={FileTextIcon}
                                description={`${contarPresentados(documentacion)} presentados`}
                                className="break-inside-avoid"
                            >
                                <ChecklistIntegrante
                                    documentos={documentacion}
                                    integranteId={integrante.id}
                                    editablePdf={
                                        integrante.activo && Boolean(configPdf)
                                    }
                                    configuracionPdf={configPdf}
                                />
                            </EducatioCard>
                        )}

                        {item.value === 'adjuntos' && configAdjuntos && (
                            <EducatioCard>
                                <AdjuntosIntegrante
                                    integranteId={integrante.id}
                                    documentos={adjuntosLibres}
                                    configuracion={configAdjuntos}
                                    editable={integrante.activo}
                                />
                            </EducatioCard>
                        )}

                        {item.value === 'otros' && (
                            <RelacionesIntegrante
                                referencias={integrante.referencias_laborales}
                                contactos={integrante.contactos}
                            />
                        )}
                    </div>
                );
            })}
        </div>
    );
}

export default function IntegranteShow({
    integrante,
    documentacion,
    filters,
    seccion: seccionInicial,
    configPdf,
    adjuntosLibres,
    configAdjuntos,
}: FichaProps) {
    const [seccion, setSeccion] = useState<SeccionIntegrante>(seccionInicial);
    const estado = useEstadoIntegrante();
    const contexto = contextoIntegrante(filters, seccion);
    const antiguedad = yearsSince(
        String(integrante.fecha_ingreso ?? ''),
        integrante.fecha_baja ?? new Date(),
    );
    const meta = [
        `Legajo ${integrante.legajo}`,
        integrante.numero_documento
            ? `${integrante.tipo_documento?.nombre ?? 'Doc.'} ${integrante.numero_documento}`
            : null,
        integrante.cuilt ? formatCuilt(String(integrante.cuilt)) : null,
        yearsSince(String(integrante.fecha_nacimiento ?? '')) !== null
            ? `${yearsSince(String(integrante.fecha_nacimiento ?? ''))} años`
            : null,
        antiguedad !== null
            ? `${antiguedad} ${antiguedad === 1 ? 'año' : 'años'} en la institución`
            : null,
    ].filter(Boolean);
    const adjuntosActivos = adjuntosLibres.filter(
        (adjunto) => adjunto.activo,
    ).length;

    return (
        <>
            <Head title={`${integrante.apellido}, ${integrante.nombre}`} />
            <div className="mx-auto flex w-full max-w-6xl flex-col gap-3">
                <EducatioCard className="px-4 py-4.5">
                    <div className="flex flex-wrap items-center gap-x-4 gap-y-3">
                        <FichaRapidaPersona
                            persona={{
                                apellido: String(integrante.apellido ?? ''),
                                nombre: String(integrante.nombre ?? ''),
                                legajo: integrante.legajo,
                                activo: integrante.activo,
                                funciones: integrante.funciones,
                                fotoUrl: integrante.tiene_foto
                                    ? foto.url(integrante.id)
                                    : null,
                            }}
                        />
                        <div className="min-w-0 flex-1">
                            <div className="flex flex-wrap items-center gap-x-2.5 gap-y-1.5">
                                <h1 className="text-xl font-semibold break-words">
                                    {integrante.apellido}, {integrante.nombre}
                                </h1>
                                <Badge
                                    variant="outline"
                                    className={
                                        integrante.activo
                                            ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                            : 'text-muted-foreground'
                                    }
                                >
                                    {integrante.activo
                                        ? 'Activo'
                                        : 'Dado de baja'}
                                </Badge>
                                {integrante.funciones.map((funcion) => (
                                    <Badge
                                        key={funcion.id}
                                        variant="outline"
                                        className="border-primary/30 bg-primary/5 text-primary"
                                    >
                                        {funcion.descripcion}
                                    </Badge>
                                ))}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground tabular-nums">
                                {meta.join(' · ')}
                            </p>
                        </div>
                        <div className="flex shrink-0 items-center gap-2">
                            {integrante.activo && (
                                <EureButtonPrimary
                                    onClick={() =>
                                        router.visit(
                                            edit(integrante.id, contexto),
                                        )
                                    }
                                >
                                    <PencilIcon aria-hidden="true" />
                                    Editar
                                </EureButtonPrimary>
                            )}
                            <EureButtonSecondary asChild>
                                <Link href={imprimir(integrante.id, contexto)}>
                                    <PrinterIcon aria-hidden="true" />
                                    Imprimir
                                </Link>
                            </EureButtonSecondary>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <EureButtonSecondary
                                        aria-label="Más acciones"
                                        disabled={estado.procesando}
                                    >
                                        <MoreVerticalIcon aria-hidden="true" />
                                    </EureButtonSecondary>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    {integrante.activo ? (
                                        <DropdownMenuItem
                                            onSelect={() =>
                                                estado.abrirBaja(integrante)
                                            }
                                        >
                                            <Trash2Icon className="text-eure-error" />
                                            Dar de baja
                                        </DropdownMenuItem>
                                    ) : (
                                        <DropdownMenuItem
                                            onSelect={() =>
                                                void estado.reincorporar(
                                                    integrante,
                                                )
                                            }
                                        >
                                            <RotateCcwIcon />
                                            Reincorporar
                                        </DropdownMenuItem>
                                    )}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    {!integrante.activo && (
                        <p
                            role="status"
                            className="mt-3 border-t pt-2.5 text-xs text-educatio-warning"
                        >
                            Dado de baja el {formatDate(integrante.fecha_baja)}.
                            Motivo: {integrante.motivo_baja}. El legajo está
                            disponible en modo consulta.
                        </p>
                    )}
                </EducatioCard>

                <PestanasIntegrante
                    value={seccion}
                    onChange={setSeccion}
                    contadores={{
                        documentacion: contarPresentados(documentacion),
                        adjuntos: adjuntosActivos
                            ? String(adjuntosActivos)
                            : undefined,
                    }}
                />

                <ContenidoFichaIntegrante
                    integrante={integrante}
                    documentacion={documentacion}
                    seccion={seccion}
                    configPdf={configPdf}
                    adjuntosLibres={adjuntosLibres}
                    configAdjuntos={configAdjuntos}
                />
            </div>
            {estado.dialogo}
        </>
    );
}
IntegranteShow.layout = ({ integrante, filters }: FichaProps) => ({
    breadcrumbs: [
        { title: 'Equipo institucional', href: index.url({ query: filters }) },
        { title: `${integrante.apellido}, ${integrante.nombre}` },
    ],
});
