import { useEffect, useRef, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import EureButtonPrimary from '@/components/ui/eure/EureButtonPrimary';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { recorteCuadrado } from '@/lib/recorte-foto';

type Props = {
    archivo: File;
    maxBytes: number;
    onConfirmar: (foto: File) => void;
    onCancelar: () => void;
};

export default function EureRecorteFoto({
    archivo,
    maxBytes,
    onConfirmar,
    onCancelar,
}: Props) {
    const canvas = useRef<HTMLCanvasElement>(null);
    const arrastre = useRef<{
        x: number;
        y: number;
        horizontal: number;
        vertical: number;
    } | null>(null);
    const [imagen, setImagen] = useState<HTMLImageElement | null>(null);
    const [zoom, setZoom] = useState(1);
    const [horizontal, setHorizontal] = useState(0.5);
    const [vertical, setVertical] = useState(0.5);
    const [error, setError] = useState('');
    const [guardando, setGuardando] = useState(false);

    useEffect(() => {
        const url = URL.createObjectURL(archivo);
        const original = new Image();
        original.onload = () => {
            if (original.naturalWidth > 6000 || original.naturalHeight > 6000) {
                setError(
                    'La imagen debe tener como máximo 6000 × 6000 píxeles.',
                );
            } else {
                setImagen(original);
            }
        };
        original.onerror = () =>
            setError(
                'No se pudo abrir la imagen. Elegí otro archivo JPG, PNG o WebP.',
            );
        original.src = url;

        return () => {
            original.onload = null;
            original.onerror = null;
            URL.revokeObjectURL(url);
        };
    }, [archivo]);

    const recorte = imagen
        ? recorteCuadrado(
              imagen.naturalWidth,
              imagen.naturalHeight,
              zoom,
              horizontal,
              vertical,
          )
        : null;
    useEffect(() => {
        const contexto = canvas.current?.getContext('2d');

        if (!contexto || !imagen) {
return;
}

        const area = recorteCuadrado(
            imagen.naturalWidth,
            imagen.naturalHeight,
            zoom,
            horizontal,
            vertical,
        );
        contexto.fillStyle = '#fff';
        contexto.fillRect(0, 0, 512, 512);
        contexto.drawImage(
            imagen,
            area.x,
            area.y,
            area.lado,
            area.lado,
            0,
            0,
            512,
            512,
        );
    }, [imagen, zoom, horizontal, vertical]);

    const confirmar = () => {
        if (!canvas.current || !imagen || guardando) {
return;
}

        setGuardando(true);
        setError('');
        canvas.current.toBlob(
            (blob) => {
                if (!blob || blob.size > maxBytes) {
                    setError(
                        'No se pudo generar la foto dentro del tamaño permitido. Probá con otra imagen.',
                    );
                    setGuardando(false);

                    return;
                }

                onConfirmar(
                    new File(
                        [blob],
                        `${archivo.name.replace(/\.[^.]+$/, '')}-foto.jpg`,
                        { type: 'image/jpeg' },
                    ),
                );
            },
            'image/jpeg',
            0.9,
        );
    };

    return (
        <Dialog
            open
            onOpenChange={(abierto) => {
                if (!abierto && !guardando) {
onCancelar();
}
            }}
        >
            <DialogContent className="max-h-[90dvh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Encuadrar foto</DialogTitle>
                    <DialogDescription>
                        Mové la imagen y ajustá el zoom. Se guardará únicamente
                        el cuadrado que ves aquí.
                    </DialogDescription>
                </DialogHeader>
                <canvas
                    ref={canvas}
                    width={512}
                    height={512}
                    aria-label="Vista previa del recorte cuadrado"
                    className="mx-auto aspect-square w-full max-w-72 cursor-move touch-none rounded border bg-muted"
                    onPointerDown={(event) => {
                        if (!imagen || guardando) {
return;
}

                        event.currentTarget.setPointerCapture(event.pointerId);
                        arrastre.current = {
                            x: event.clientX,
                            y: event.clientY,
                            horizontal,
                            vertical,
                        };
                    }}
                    onPointerMove={(event) => {
                        if (
                            !arrastre.current ||
                            !imagen ||
                            !recorte ||
                            guardando
                        ) {
return;
}

                        const escala =
                            recorte.lado /
                            event.currentTarget.getBoundingClientRect().width;
                        const rangoX = imagen.naturalWidth - recorte.lado;
                        const rangoY = imagen.naturalHeight - recorte.lado;
                        const limitar = (valor: number) =>
                            Math.max(0, Math.min(1, valor));

                        if (rangoX > 0) {
setHorizontal(
                                limitar(
                                    arrastre.current.horizontal -
                                        ((event.clientX - arrastre.current.x) *
                                            escala) /
                                            rangoX,
                                ),
                            );
}

                        if (rangoY > 0) {
setVertical(
                                limitar(
                                    arrastre.current.vertical -
                                        ((event.clientY - arrastre.current.y) *
                                            escala) /
                                            rangoY,
                                ),
                            );
}
                    }}
                    onPointerUp={() => {
                        arrastre.current = null;
                    }}
                    onPointerCancel={() => {
                        arrastre.current = null;
                    }}
                    onLostPointerCapture={() => {
                        arrastre.current = null;
                    }}
                />
                {!imagen && !error && (
                    <p role="status" className="text-sm text-muted-foreground">
                        Cargando imagen…
                    </p>
                )}
                <div className="space-y-3">
                    {[
                        {
                            id: 'zoom',
                            label: 'Zoom',
                            value: zoom,
                            min: 1,
                            max: 4,
                            change: setZoom,
                            disabled: false,
                        },
                        {
                            id: 'horizontal',
                            label: 'Posición horizontal',
                            value: horizontal,
                            min: 0,
                            max: 1,
                            change: setHorizontal,
                            disabled: recorte?.lado === imagen?.naturalWidth,
                        },
                        {
                            id: 'vertical',
                            label: 'Posición vertical',
                            value: vertical,
                            min: 0,
                            max: 1,
                            change: setVertical,
                            disabled: recorte?.lado === imagen?.naturalHeight,
                        },
                    ].map((control) => (
                        <div key={control.id} className="space-y-1">
                            <label
                                htmlFor={`recorte-${control.id}`}
                                className="block text-sm font-medium"
                            >
                                {control.label}
                            </label>
                            <input
                                id={`recorte-${control.id}`}
                                type="range"
                                min={control.min}
                                max={control.max}
                                step={0.01}
                                value={control.value}
                                disabled={
                                    !imagen || guardando || control.disabled
                                }
                                onChange={(event) =>
                                    control.change(Number(event.target.value))
                                }
                                className="block w-full accent-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:opacity-40"
                            />
                        </div>
                    ))}
                </div>
                {error && (
                    <p role="alert" className="text-sm text-eure-error">
                        {error}
                    </p>
                )}
                <DialogFooter>
                    <EureButtonSecondary
                        type="button"
                        disabled={guardando}
                        onClick={onCancelar}
                    >
                        Cancelar
                    </EureButtonSecondary>
                    <EureButtonPrimary
                        type="button"
                        disabled={!imagen}
                        loading={guardando}
                        onClick={confirmar}
                    >
                        Usar recorte
                    </EureButtonPrimary>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
