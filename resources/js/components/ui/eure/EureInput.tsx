import { InputMask } from '@react-input/mask';
import { CircleAlertIcon } from 'lucide-react';
import type { InputHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

interface EureInputProps extends InputHTMLAttributes<HTMLInputElement> {
    error?: boolean;
    mask?: string;
    replacement?: Record<string, RegExp>;
}

export default function EureInput({
    className,
    error = false,
    mask,
    replacement = { _: /\d/ },
    ...props
}: EureInputProps) {
    const inputClass = cn(
        'w-full rounded-md border px-3 py-2 text-sm outline-none transition-colors',
        'border-[var(--eure-border)] bg-background text-[var(--eure-text)]',
        'placeholder:text-[var(--eure-sub)]',
        'focus:border-[var(--eure-primary)] focus:ring-2 focus:ring-[var(--eure-primary)]',
        error && 'border-[var(--eure-error-border)] pr-9 focus:ring-[var(--eure-error)]',
        className,
    );

    return (
        <div className="relative w-full">
            {mask ? (
                <InputMask
                    mask={mask}
                    replacement={replacement}
                    {...props}
                    aria-invalid={error || undefined}
                    className={inputClass}
                />
            ) : (
                <input {...props} aria-invalid={error || undefined} className={inputClass} />
            )}
            {error && (
                <CircleAlertIcon
                    className="absolute top-1/2 right-3 size-4 -translate-y-1/2 text-(--eure-error)"
                    aria-hidden="true"
                />
            )}
        </div>
    );
}
