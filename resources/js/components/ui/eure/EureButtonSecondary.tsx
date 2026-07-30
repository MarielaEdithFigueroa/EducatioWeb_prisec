import type { ComponentProps } from 'react';

import { Button } from '@/components/ui/button';

export default function EureButtonSecondary({
    children,
    type = 'button',
    ...props
}: ComponentProps<typeof Button>) {
    return (
        <Button variant="outline" type={type} {...props}>
            {children}
        </Button>
    );
}
