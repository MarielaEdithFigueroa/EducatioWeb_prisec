import type { KeyboardEvent } from 'react';
import { useRef } from 'react';
import { cn } from '@/lib/utils';
import { SECCIONES } from './types';
import type { SeccionIntegrante } from './types';

export default function PestanasIntegrante({
    value,
    onChange,
    contadores = {},
}: {
    value: SeccionIntegrante;
    onChange: (value: SeccionIntegrante) => void;
    /** Texto corto al lado del label, por ejemplo «6/9» o «3». */
    contadores?: Partial<Record<SeccionIntegrante, string>>;
}) {
    const refs = useRef<Array<HTMLButtonElement | null>>([]);
    const navegar = (
        event: KeyboardEvent<HTMLButtonElement>,
        indice: number,
    ) => {
        let siguiente: number | null = null;

        if (event.key === 'ArrowRight') {
            siguiente = (indice + 1) % SECCIONES.length;
        }

        if (event.key === 'ArrowLeft') {
            siguiente = (indice + SECCIONES.length - 1) % SECCIONES.length;
        }

        if (event.key === 'Home') {
            siguiente = 0;
        }

        if (event.key === 'End') {
            siguiente = SECCIONES.length - 1;
        }

        if (siguiente !== null) {
            event.preventDefault();
            refs.current[siguiente]?.focus();
        }
    };

    return (
        <div className="overflow-x-auto border-b border-border print:hidden">
            <div
                role="tablist"
                aria-label="Secciones del legajo"
                className="flex min-w-max gap-1"
            >
                {SECCIONES.map((seccion, i) => (
                    <button
                        key={seccion.value}
                        ref={(el) => {
                            refs.current[i] = el;
                        }}
                        type="button"
                        id={`tab-integrante-${seccion.value}`}
                        role="tab"
                        aria-selected={value === seccion.value}
                        aria-controls={`panel-integrante-${seccion.value}`}
                        tabIndex={value === seccion.value ? 0 : -1}
                        onKeyDown={(event) => navegar(event, i)}
                        onClick={() => onChange(seccion.value)}
                        className={cn(
                            'flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap outline-none focus-visible:ring-2 focus-visible:ring-ring',
                            value === seccion.value
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:bg-muted',
                        )}
                    >
                        {seccion.label}
                        {contadores[seccion.value] && (
                            <span className="text-xs font-normal text-muted-foreground tabular-nums">
                                {contadores[seccion.value]}
                            </span>
                        )}
                    </button>
                ))}
            </div>
        </div>
    );
}
