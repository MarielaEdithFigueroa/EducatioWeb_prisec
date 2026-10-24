import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon, PrinterIcon } from 'lucide-react';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { formatDate } from '@/lib/date';
import { show } from '@/routes/academico/integrantes';
import { ContenidoFichaIntegrante } from './show';
import type { FichaProps } from './show';
import { contextoIntegrante } from './types';

export default function ImprimirIntegrante({
    integrante,
    documentacion,
    filters,
    seccion,
}: FichaProps) {
    return (
        <>
            <Head
                title={`Ficha del integrante · ${integrante.apellido}, ${integrante.nombre}`}
            />
            <main className="mx-auto max-w-5xl space-y-6 bg-background p-6 text-foreground print:max-w-none print:bg-white print:p-0 print:text-black">
                <div className="flex gap-2 print:hidden">
                    <EureButtonSecondary asChild>
                        <Link
                            href={show(
                                integrante.id,
                                contextoIntegrante(filters, seccion),
                            )}
                        >
                            <ArrowLeftIcon />
                            Volver a la ficha
                        </Link>
                    </EureButtonSecondary>
                    <EureButtonPrimary onClick={() => window.print()}>
                        <PrinterIcon />
                        Imprimir
                    </EureButtonPrimary>
                </div>
                <header className="border-b pb-4">
                    <h1 className="text-2xl font-semibold">
                        Ficha del integrante
                    </h1>
                    <p className="mt-2 text-lg">
                        {integrante.apellido}, {integrante.nombre}
                    </p>
                    <p>
                        Legajo {integrante.legajo} ·{' '}
                        {integrante.funciones
                            .map((funcion) => funcion.descripcion)
                            .join(' · ') || 'Sin funciones asignadas'}{' '}
                        · {integrante.activo ? 'Activo' : 'Dado de baja'}
                    </p>
                    {!integrante.activo && (
                        <p>
                            Baja: {formatDate(integrante.fecha_baja)} ·{' '}
                            {integrante.motivo_baja}
                        </p>
                    )}
                </header>
                <ContenidoFichaIntegrante
                    integrante={integrante}
                    documentacion={documentacion}
                />
            </main>
        </>
    );
}
