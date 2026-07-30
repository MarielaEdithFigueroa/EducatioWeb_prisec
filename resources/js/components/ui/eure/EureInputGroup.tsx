import type { InputHTMLAttributes } from 'react';

import EureInput from './EureInput';

interface EureInputGroupProps extends InputHTMLAttributes<HTMLInputElement> {
    label: string;
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
            <label
                htmlFor={name}
                className={`block text-sm font-semibold tracking-wide mb-1.5 text-(--eure-text) ${classNameLabel}`}
            >
                {label}
            </label>

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
                <p className="mt-1 text-xs font-medium text-(--eure-error)">{errorMessage}</p>
            ) : helperText ? (
                <p className="mt-1 text-xs italic text-(--eure-sub)">{helperText}</p>
            ) : null}
        </div>
    );
}
