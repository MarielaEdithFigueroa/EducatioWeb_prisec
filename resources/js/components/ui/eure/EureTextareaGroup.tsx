import type { TextareaHTMLAttributes } from 'react';

import EureTextarea from './EureTextarea';

interface EureTextareaGroupProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    label: string;
    error?: boolean;
    errorMessage?: string;
    helperText?: string;
    name: string;
}

export default function EureTextareaGroup({
    label,
    error = false,
    errorMessage = '',
    helperText = '',
    name,
    className = '',
    ...props
}: EureTextareaGroupProps) {
    const inputClasses = className
        .split(' ')
        .filter((cls) => cls.startsWith('text-'))
        .join(' ');

    const wrapperClasses = className
        .split(' ')
        .filter((cls) => !cls.startsWith('text-'))
        .join(' ');

    return (
        <div className={wrapperClasses}>
            <label
                htmlFor={name}
                className={`block text-sm font-semibold tracking-wide mb-1.5 text-[var(--eure-text)] ${inputClasses}`}
            >
                {label}
            </label>

            <EureTextarea
                id={name}
                name={name}
                error={error}
                className={inputClasses}
                {...props}
            />

            {errorMessage && error ? (
                <p className="mt-1 text-xs font-medium text-[var(--eure-error)]">{errorMessage}</p>
            ) : helperText ? (
                <p className="mt-1 text-xs italic text-[var(--eure-sub)]">{helperText}</p>
            ) : null}
        </div>
    );
}
