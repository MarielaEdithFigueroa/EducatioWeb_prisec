import { CircleAlertIcon } from 'lucide-react';
import type { TextareaHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

interface EureTextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    error?: boolean;
}

export default function EureTextarea({ className, error = false, ...props }: EureTextareaProps) {
    return (
        <div className="relative w-full">
            <textarea
                {...props}
                aria-invalid={error || undefined}
                className={cn(
                    'w-full rounded-md border px-3 py-2 text-sm outline-none transition-colors',
                    'border-[var(--eure-border)] bg-background text-[var(--eure-text)]',
                    'placeholder:text-[var(--eure-sub)]',
                    'focus:border-[var(--eure-primary)] focus:ring-2 focus:ring-[var(--eure-primary)]',
                    error && 'border-[var(--eure-error-border)] pr-9 focus:ring-[var(--eure-error)]',
                    className,
                )}
            />
            {error && (
                <CircleAlertIcon
                    className="absolute top-3 right-3 size-4 text-[var(--eure-error)]"
                    aria-hidden="true"
                />
            )}
        </div>
    );
}
