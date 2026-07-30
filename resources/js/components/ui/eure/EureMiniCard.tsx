import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';

interface EureMiniCardProps {
    title: string;
    value: string | number | ReactNode;
    className?: string;
}

export default function EureMiniCard({ title, value, className }: EureMiniCardProps) {
    return (
        <div
            className={cn(
                'rounded-lg border border-[var(--eure-border)] bg-background px-4 py-3 shadow-sm',
                className,
            )}
        >
            <p className="text-xs text-[var(--eure-sub)]">{title}</p>
            <p className="mt-1 text-2xl font-bold text-[var(--eure-text)]">{value}</p>
        </div>
    );
}
