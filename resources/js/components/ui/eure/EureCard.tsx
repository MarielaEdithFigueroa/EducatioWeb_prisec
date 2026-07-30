import type { CSSProperties, ReactNode } from 'react';

import { cn } from '@/lib/utils';

interface EureCardProps {
    title?: string;
    children: ReactNode;
    className?: string;
    style?: CSSProperties;
}

export default function EureCard({
    title,
    children,
    className,
    style,
}: EureCardProps) {
    return (
        <div
            className={cn(
                'rounded-lg border border-[var(--eure-border)] bg-background p-4 shadow-sm',
                className,
            )}
            style={style}
        >
            {title && (
                <h3 className="-mx-4 -mt-4 mb-4 rounded-t-lg border-b border-[var(--eure-border)] bg-[var(--eure-bg)] px-4 py-2 text-base font-semibold text-[var(--eure-text)]">
                    {title}
                </h3>
            )}
            {children}
        </div>
    );
}
