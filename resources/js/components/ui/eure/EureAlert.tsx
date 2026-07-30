import { CircleCheckIcon, CircleXIcon, InfoIcon, TriangleAlertIcon, XIcon } from 'lucide-react';
import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';

type AlertType = 'success' | 'error' | 'warning' | 'info';

interface EureAlertProps {
    type?: AlertType;
    message: string;
    className?: string;
    onClose?: () => void;
}

const ICONS: Record<AlertType, ReactNode> = {
    success: <CircleCheckIcon className="size-5 shrink-0" />,
    error: <CircleXIcon className="size-5 shrink-0" />,
    warning: <TriangleAlertIcon className="size-5 shrink-0" />,
    info: <InfoIcon className="size-5 shrink-0" />,
};

const STYLES: Record<AlertType, string> = {
    success:
        'bg-[var(--eure-success-bg)] text-[var(--eure-success)] border border-[var(--eure-success)]',
    error: 'bg-red-50 text-[var(--eure-error)] border border-[var(--eure-error)]',
    warning:
        'bg-[var(--eure-warning-bg)] text-[var(--eure-warning)] border border-[var(--eure-warning)]',
    info: 'bg-[var(--eure-info-bg)] text-[var(--eure-info)] border border-[var(--eure-info)]',
};

export default function EureAlert({
    type = 'error',
    message,
    className,
    onClose,
}: EureAlertProps) {
    return (
        <div
            role="alert"
            className={cn('flex items-start gap-3 rounded-lg px-4 py-3', STYLES[type], className)}
        >
            <span className="mt-0.5">{ICONS[type]}</span>
            <p className="flex-1 text-sm font-medium">{message}</p>
            {onClose && (
                <button
                    onClick={onClose}
                    aria-label="Cerrar"
                    className="mt-0.5 opacity-70 transition-opacity hover:opacity-100"
                >
                    <XIcon className="size-4" />
                </button>
            )}
        </div>
    );
}
