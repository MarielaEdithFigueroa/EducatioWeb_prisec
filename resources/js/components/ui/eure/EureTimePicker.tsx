import { CircleAlertIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

interface EureTimePickerProps {
    value?: string;
    onChange?: (value: string) => void;
    error?: boolean;
    disabled?: boolean;
    className?: string;
    id?: string;
}

export default function EureTimePicker({
    value,
    onChange,
    error = false,
    disabled = false,
    className,
    id,
}: EureTimePickerProps) {
    return (
        <div className={cn('relative w-full', className)}>
            <input
                id={id}
                type="time"
                value={value}
                onChange={(e) => onChange?.(e.target.value)}
                disabled={disabled}
                aria-invalid={error || undefined}
                className={cn(
                    'w-full rounded-md border px-3 py-2 text-sm outline-none transition-colors',
                    'border-[var(--eure-border)] bg-background text-[var(--eure-text)]',
                    'focus:border-[var(--eure-primary)] focus:ring-2 focus:ring-[var(--eure-primary)]',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    error && 'border-[var(--eure-error-border)] pr-9 focus:ring-[var(--eure-error)]',
                )}
            />
            {error && (
                <CircleAlertIcon
                    className="absolute top-1/2 right-3 size-4 -translate-y-1/2 text-[var(--eure-error)]"
                    aria-hidden="true"
                />
            )}
        </div>
    );
}
