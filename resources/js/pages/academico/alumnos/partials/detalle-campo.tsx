import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import EducatioCard from '@/components/educatio-card';
import { cn } from '@/lib/utils';

export function BloqueDetalle({
    title,
    description,
    icon,
    children,
}: {
    title: string;
    description?: string;
    icon?: LucideIcon;
    children: ReactNode;
}) {
    return (
        <EducatioCard title={title} description={description} icon={icon}>
            <dl className="grid gap-x-6 gap-y-3 md:grid-cols-2 xl:grid-cols-3">
                {children}
            </dl>
        </EducatioCard>
    );
}

export default function DetalleCampo({
    label,
    value,
    className,
}: {
    label: string;
    value: ReactNode;
    className?: string;
}) {
    const contenido =
        value === null || value === undefined || value === '' ? '—' : value;

    return (
        <div className={cn('min-w-0', className)}>
            <dt className="text-sm font-medium text-foreground">{label}</dt>
            <dd className="mt-1 text-sm break-words text-muted-foreground">
                {contenido}
            </dd>
        </div>
    );
}
