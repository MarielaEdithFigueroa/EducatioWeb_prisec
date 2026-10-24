import type { InputHTMLAttributes, ReactNode } from 'react';

import EureInput from './EureInput';

interface EureInputGroupProps extends InputHTMLAttributes<HTMLInputElement> {
    label: ReactNode;
    labelAction?: ReactNode;
    error?: boolean;
    errorMessage?: string;
    helperText?: string;
    name: string;
    mask?: string;
    replacement?: Record<string, RegExp>;
    className?: string;
    classNameInput?: string;
    classNameLabel?: string;
    classNameWrapper?: string;
}

export default function EureInputGroup({
    label,
    labelAction,
    error = false,
    errorMessage = '',
    helperText = '',
    name,
    mask,
    replacement,
    className = '',
    classNameInput = '',
    classNameLabel = '',
    classNameWrapper = '',
    ...props
}: EureInputGroupProps) {
    return (
        <div className={`${className} ${classNameWrapper}`}>
            <div className="mb-1.5 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                <label
                    htmlFor={name}
                    className={`block text-sm font-semibold tracking-wide text-(--eure-text) ${classNameLabel}`}
                >
                    {label}
                </label>
                {labelAction}
            </div>

            <EureInput
                id={name}
                name={name}
                error={error}
                mask={mask}
                replacement={replacement}
                className={classNameInput}
                {...props}
            />

            {errorMessage && error ? (
                <p className="mt-1 text-xs font-medium text-(--eure-error)">
                    {errorMessage}
                </p>
            ) : helperText ? (
                <p className="mt-1 text-xs text-(--eure-sub) italic">
                    {helperText}
                </p>
            ) : null}
        </div>
    );
}
