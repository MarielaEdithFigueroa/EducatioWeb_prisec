import type { ComponentProps } from 'react';
import EureCard from '@/components/ui/eure/EureCard';
import { cn } from '@/lib/utils';

type EducatioCardProps = Omit<ComponentProps<typeof EureCard>, 'variant'>;

export default function EducatioCard({
    className,
    headingLevel = 'h2',
    ...props
}: EducatioCardProps) {
    return (
        <EureCard
            {...props}
            variant="plain"
            headingLevel={headingLevel}
            className={cn(
                'rounded-sm [&_header_svg]:text-educatio-icon',
                className,
            )}
        />
    );
}
