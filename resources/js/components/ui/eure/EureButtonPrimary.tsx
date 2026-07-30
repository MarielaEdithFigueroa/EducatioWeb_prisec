import type { ButtonHTMLAttributes, ReactNode } from 'react';

import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

interface EureButtonPrimaryProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    children: ReactNode;
    loading?: boolean;
    loadingPosition?: 'left' | 'right';
}

export default function EureButtonPrimary({
    children,
    className,
    loading = false,
    loadingPosition = 'left',
    disabled,
    type = 'button',
    ...props
}: EureButtonPrimaryProps) {
    return (
        <button
            type={type}
            disabled={loading || disabled}
            className={cn(
                'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors',
                'bg-[var(--eure-primary)] hover:bg-[var(--eure-primary-strong)]',
                'disabled:cursor-not-allowed disabled:opacity-50',
                className,
            )}
            {...props}
        >
            {loading && loadingPosition === 'left' && (
                <Spinner className="size-4" />
            )}
            {children}
            {loading && loadingPosition === 'right' && (
                <Spinner className="size-4" />
            )}
        </button>
    );
}
