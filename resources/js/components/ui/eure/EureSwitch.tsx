import type { ComponentProps } from 'react';

import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

interface EureSwitchProps extends ComponentProps<typeof Switch> {
    label?: string;
}

export default function EureSwitch({ id, label, ...props }: EureSwitchProps) {
    return (
        <div className="flex items-center gap-2">
            <Switch id={id} {...props} />
            {label && <Label htmlFor={id}>{label}</Label>}
        </div>
    );
}
