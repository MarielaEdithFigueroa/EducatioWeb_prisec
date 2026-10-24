import type { KeyboardEvent } from 'react';
import { useRef } from 'react';
import { cn } from '@/lib/utils';
import type { SeccionAlumno } from './types';

const PESTANAS: Array<{ value: SeccionAlumno; label: string }> = [
    { value: 'personal', label: 'Datos personales' },
    { value: 'institucional', label: 'Datos institucionales' },
    { value: 'responsables', label: 'Responsables' },
    { value: 'documentacion', label: 'Documentación' },
];

export default function PestanasAlumno({
    value,
    onValueChange,
}: {
    value: SeccionAlumno;
    onValueChange: (value: SeccionAlumno) => void;
}) {
    const referencias = useRef<Array<HTMLButtonElement | null>>([]);

    const moverFoco = (
        event: KeyboardEvent<HTMLButtonElement>,
        indice: number,
    ) => {
        let siguiente: number | null = null;

        if (event.key === 'ArrowRight') {
            siguiente = (indice + 1) % PESTANAS.length;
        } else if (event.key === 'ArrowLeft') {
            siguiente = (indice - 1 + PESTANAS.length) % PESTANAS.length;
        } else if (event.key === 'Home') {
            siguiente = 0;
        } else if (event.key === 'End') {
            siguiente = PESTANAS.length - 1;
        }

        if (siguiente !== null) {
            event.preventDefault();
            referencias.current[siguiente]?.focus();
        }
    };

    return (
        <div className="overflow-x-auto border-b border-border">
            <div
                role="tablist"
                aria-label="Secciones del alumno"
                className="flex min-w-max gap-1"
            >
                {PESTANAS.map((pestana, indice) => {
                    const activa = pestana.value === value;

                    return (
                        <button
                            key={pestana.value}
                            ref={(elemento) => {
                                referencias.current[indice] = elemento;
                            }}
                            id={`tab-alumno-${pestana.value}`}
                            type="button"
                            role="tab"
                            aria-selected={activa}
                            aria-controls={`panel-alumno-${pestana.value}`}
                            tabIndex={activa ? 0 : -1}
                            onClick={() => onValueChange(pestana.value)}
                            onKeyDown={(event) => moverFoco(event, indice)}
                            className={cn(
                                'relative px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors outline-none',
                                'focus-visible:rounded-t-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                                activa
                                    ? 'text-primary after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:bg-primary'
                                    : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground',
                            )}
                        >
                            {pestana.label}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
