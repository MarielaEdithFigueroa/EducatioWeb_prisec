import { UserRoundIcon } from 'lucide-react';
import { useRef } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { cn } from '@/lib/utils';

export type PersonaFichaRapida = {
    apellido: string;
    nombre: string;
    legajo: string | number | null;
    activo: boolean;
    fotoUrl?: string | null;
    funciones: Array<{ id: number; descripcion: string }>;
};

function RetratoPersona({
    persona,
    className,
}: {
    persona: PersonaFichaRapida;
    className?: string;
}) {
    return (
        <Avatar className={cn('rounded-md border bg-muted', className)}>
            {persona.fotoUrl && (
                <AvatarImage
                    src={persona.fotoUrl}
                    alt={`Foto de ${persona.apellido}, ${persona.nombre}`}
                    className="object-contain"
                />
            )}
            <AvatarFallback
                className="rounded-md text-muted-foreground"
                role="img"
                aria-label={`Sin foto de ${persona.apellido}, ${persona.nombre}`}
            >
                <UserRoundIcon className="size-2/3" aria-hidden="true" />
            </AvatarFallback>
        </Avatar>
    );
}

export default function FichaRapidaPersona({
    persona,
    className,
}: {
    persona: PersonaFichaRapida;
    /** Tamaño de la foto que abre la ficha; no modifica el retrato del modal. */
    className?: string;
}) {
    const titulo = useRef<HTMLHeadingElement>(null);
    const nombreCompleto = `${persona.apellido}, ${persona.nombre}`;

    return (
        <Dialog>
            <DialogTrigger asChild>
                <button
                    type="button"
                    aria-label={`Ver ficha rápida de ${nombreCompleto}`}
                    title="Ver ficha rápida"
                    className={cn(
                        'size-21 shrink-0 cursor-pointer rounded-md transition-opacity hover:opacity-85 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none',
                        className,
                    )}
                >
                    <RetratoPersona persona={persona} className="size-full" />
                </button>
            </DialogTrigger>
            <DialogContent
                className="flex max-h-[90dvh] flex-col gap-0 overflow-hidden p-0 sm:max-w-2xl"
                onOpenAutoFocus={(event) => {
                    event.preventDefault();
                    titulo.current?.focus();
                }}
            >
                <DialogHeader className="shrink-0 border-b px-6 py-5 pr-12 text-left">
                    <DialogTitle
                        ref={titulo}
                        tabIndex={-1}
                        className="text-xl leading-snug break-words outline-none"
                    >
                        {nombreCompleto}
                    </DialogTitle>
                    <DialogDescription>
                        Ficha rápida · Legajo {persona.legajo || '—'}
                    </DialogDescription>
                </DialogHeader>
                <div className="min-h-0 overflow-y-auto p-6">
                    <div className="grid items-start gap-6 sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
                        <RetratoPersona
                            persona={persona}
                            className="mx-auto aspect-square size-auto w-full max-w-80"
                        />
                        <div className="min-w-0 space-y-5">
                            <Badge
                                variant="outline"
                                className={
                                    persona.activo
                                        ? 'border-eure-success/30 bg-eure-success/10 text-eure-success'
                                        : 'text-muted-foreground'
                                }
                            >
                                {persona.activo ? 'Activo' : 'Dado de baja'}
                            </Badge>
                            <section
                                aria-label="Funciones de la persona"
                                className="space-y-3"
                            >
                                <h3 className="font-semibold">Funciones</h3>
                                {persona.funciones.length ? (
                                    <ul className="flex flex-wrap gap-2">
                                        {persona.funciones.map((funcion) => (
                                            <li
                                                key={funcion.id}
                                                className="min-w-0"
                                            >
                                                <Badge
                                                    variant="outline"
                                                    className="max-w-full border-primary/30 bg-primary/5 px-3 py-1.5 text-sm whitespace-normal text-primary"
                                                >
                                                    {funcion.descripcion}
                                                </Badge>
                                            </li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        Sin funciones asignadas.
                                    </p>
                                )}
                            </section>
                        </div>
                    </div>
                </div>
                <DialogFooter className="shrink-0 border-t bg-muted/30 px-6 py-4">
                    <DialogClose asChild>
                        <EureButtonSecondary>Cerrar</EureButtonSecondary>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
