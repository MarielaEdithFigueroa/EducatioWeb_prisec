import * as React from 'react';
import { createRoot } from 'react-dom/client';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface ConfirmOptions {
    title: string;
    text?: string;
    confirmText?: string;
    cancelText?: string;
}

function ConfirmHost({
    opts,
    resolve,
}: {
    opts: ConfirmOptions;
    resolve: (value: boolean) => void;
}) {
    const [open, setOpen] = React.useState(true);

    const close = (value: boolean) => {
        setOpen(false);
        resolve(value);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(isOpen) => {
                if (!isOpen) {
                    close(false);
                }
            }}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{opts.title}</DialogTitle>
                    {opts.text && <DialogDescription>{opts.text}</DialogDescription>}
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" onClick={() => close(false)}>
                        {opts.cancelText ?? 'Cancelar'}
                    </Button>
                    <Button onClick={() => close(true)}>{opts.confirmText ?? 'Confirmar'}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

export default function EureConfirmSwal() {
    return (opts: ConfirmOptions): Promise<boolean> =>
        new Promise<boolean>((resolve) => {
            const mount = document.createElement('div');
            document.body.appendChild(mount);
            const root = createRoot(mount);

            const settle = (value: boolean) => {
                resolve(value);
                setTimeout(() => {
                    root.unmount();
                    mount.remove();
                }, 200);
            };

            root.render(<ConfirmHost opts={opts} resolve={settle} />);
        });
}
