import { Info, Phone, Smartphone } from 'lucide-react';
import type { ChangeEventHandler } from 'react';

import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';

import EureInputGroup from './EureInputGroup';

export interface EureTelefonoInputGroupProps {
    value: string;
    onChange: ChangeEventHandler<HTMLInputElement>;
    esLineaFija: boolean;
    onEsLineaFijaChange?: (esLineaFija: boolean) => void;
    label?: string;
    name?: string;
    error?: boolean;
    errorMessage?: string;
    className?: string;
}

export default function EureTelefonoInputGroup({
    value,
    onChange,
    esLineaFija,
    onEsLineaFijaChange,
    name = 'telefono',
    label = 'Teléfono',
    error = false,
    errorMessage,
    className,
}: EureTelefonoInputGroupProps) {
    const TelefonoIcon = esLineaFija ? Phone : Smartphone;

    return (
        <EureInputGroup
            className={className}
            label={
                <span className="inline-flex items-center gap-1.5">
                    <TelefonoIcon aria-hidden="true" className="size-3.5" />
                    {label}
                </span>
            }
            labelAction={
                <span className="flex items-baseline gap-1.5 text-xs text-(--eure-sub)">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <button
                                type="button"
                                tabIndex={-1}
                                aria-label={`Ayuda sobre ${label.toLowerCase()}`}
                                className="inline-flex size-4 cursor-help items-center justify-center rounded-full transition-colors hover:text-(--eure-text) focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <Info aria-hidden="true" className="size-3.5" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent
                            side="top"
                            className="max-w-72 text-pretty"
                        >
                            Ingresá el número con la característica, por ejemplo
                            299 5123456. Para otro país, incluí el prefijo
                            internacional.
                            {onEsLineaFijaChange &&
                                ' Podés indicar si es línea fija o móvil usando «cambiar».'}
                        </TooltipContent>
                    </Tooltip>
                    <span>{esLineaFija ? 'Línea fija' : 'Línea móvil'}</span>
                    {onEsLineaFijaChange && (
                        <button
                            type="button"
                            tabIndex={-1}
                            aria-pressed={esLineaFija}
                            aria-label={`Cambiar ${label.toLowerCase()} a línea ${esLineaFija ? 'móvil' : 'fija'}`}
                            className="cursor-pointer font-medium underline underline-offset-2 transition-colors hover:text-(--eure-text) focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            onClick={() => onEsLineaFijaChange(!esLineaFija)}
                        >
                            (cambiar)
                        </button>
                    )}
                </span>
            }
            name={name}
            type="tel"
            inputMode="tel"
            autoComplete="tel"
            maxLength={40}
            value={value}
            onChange={onChange}
            placeholder="299 5123456"
            error={error}
            errorMessage={errorMessage}
        />
    );
}
