import type { LucideIcon } from 'lucide-react';
import type { CSSProperties, ReactNode } from 'react';

import { cn } from '@/lib/utils';

interface EureCardProps {
    title?: string;
    description?: string;
    icon?: LucideIcon;
    variant?: 'default' | 'plain';
    headingLevel?: 'h2' | 'h3';
    children: ReactNode;
    className?: string;
    style?: CSSProperties;
}

export default function EureCard({
    title,
    description,
    icon: Icon,
    variant = 'default',
    headingLevel: Heading = 'h3',
    children,
    className,
    style,
}: EureCardProps) {
    return (
        <div
            className={cn(
                'rounded-lg border border-[var(--eure-border)] bg-background p-4 shadow-sm',
                variant === 'plain' &&
                    'bg-card text-card-foreground shadow-none',
                className,
            )}
            style={style}
        >
            {title && (
                <header
                    className={cn(
                        '-mx-4 -mt-4 mb-4 rounded-t-lg border-b border-[var(--eure-border)] bg-[var(--eure-bg)] px-4 py-2',
                        variant === 'plain' &&
                            'mx-0 mt-0 border-0 bg-transparent p-0',
                    )}
                >
                    <Heading className="flex items-center gap-2 text-base font-semibold text-card-foreground">
                        {Icon && (
                            <Icon
                                className="size-5 shrink-0 text-primary"
                                aria-hidden="true"
                            />
                        )}
                        {title}
                    </Heading>
                    {description && (
                        <p className="mt-1 text-sm text-muted-foreground">
                            {description}
                        </p>
                    )}
                </header>
            )}
            {children}
        </div>
    );
}
