import { FileIcon, UploadIcon, XIcon } from 'lucide-react';
import type { DragEvent } from 'react';
import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import EureButtonSecondary from '@/components/ui/eure/EureButtonSecondary';
import { formatFileSize } from '@/lib/files';
import { cn } from '@/lib/utils';
import type { ConfigAdjuntos } from '@/types/adjuntos';

type Props = {
    name: string;
    configuracion: ConfigAdjuntos;
    value: File | null;
    onChange: (archivo: File | null) => void;
    error?: string;
    disabled?: boolean;
};

export default function EureArchivoGroup({
    name,
    configuracion,
    value,
    onChange,
    error,
    disabled,
}: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const profundidadArrastre = useRef(0);
    const [arrastrando, setArrastrando] = useState(false);
    const [errorLocal, setErrorLocal] = useState<string | null>(null);
    const mensajeError = errorLocal || error;
    const ayudaId = `${name}_ayuda`;
    const errorId = `${name}_error`;

    const seleccionar = (archivos: File[]) => {
        if (disabled || archivos.length === 0) {
            return;
        }

        const archivo = archivos[0];
        const extension = archivo.name.split('.').pop()?.toLowerCase();
        let mensaje: string | null = null;

        if (archivos.length !== 1) {
            mensaje = 'Adjuntá un archivo por vez.';
        } else if (!configuracion.extensiones.includes(extension ?? '')) {
            mensaje = 'El tipo de archivo no está permitido.';
        } else if (archivo.size > configuracion.max_bytes) {
            mensaje = `El archivo supera el máximo de ${formatFileSize(configuracion.max_bytes)}.`;
        }

        setErrorLocal(mensaje);
        onChange(mensaje ? null : archivo);
    };

    const recibirArchivo = (event: DragEvent<HTMLDivElement>) => {
        event.preventDefault();
        event.stopPropagation();
        profundidadArrastre.current = 0;
        setArrastrando(false);

        if (disabled) {
            return;
        }

        const contieneCarpeta = Array.from(event.dataTransfer.items).some(
            (item) => item.webkitGetAsEntry?.()?.isDirectory,
        );

        if (contieneCarpeta) {
            setErrorLocal('Seleccioná un archivo, no una carpeta.');
            onChange(null);

            return;
        }

        seleccionar(Array.from(event.dataTransfer.files));
    };

    return (
        <div>
            <label
                htmlFor={name}
                className="mb-1.5 block text-sm font-semibold tracking-wide text-(--eure-text)"
            >
                Archivo
            </label>
            <div
                role="group"
                aria-label="Adjuntar archivo"
                className={cn(
                    'rounded-sm border-2 border-dashed border-border bg-muted/20 p-5 text-center transition-colors',
                    arrastrando && !disabled && 'border-primary bg-primary/10',
                    mensajeError && 'border-eure-error',
                    disabled && 'opacity-60',
                )}
                onDragEnter={(event) => {
                    event.preventDefault();

                    if (
                        !disabled &&
                        event.dataTransfer.types.includes('Files')
                    ) {
                        profundidadArrastre.current += 1;
                        setArrastrando(true);
                    }
                }}
                onDragOver={(event) => {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = disabled ? 'none' : 'copy';
                }}
                onDragLeave={(event) => {
                    event.preventDefault();
                    profundidadArrastre.current = Math.max(
                        0,
                        profundidadArrastre.current - 1,
                    );

                    if (profundidadArrastre.current === 0) {
                        setArrastrando(false);
                    }
                }}
                onDrop={recibirArchivo}
            >
                <UploadIcon
                    className="mx-auto mb-2 size-7 text-muted-foreground"
                    aria-hidden="true"
                />
                <p className="text-sm font-medium">
                    {arrastrando && !disabled
                        ? 'Soltá el archivo aquí'
                        : 'Arrastrá y soltá un archivo aquí'}
                </p>
                <p className="mt-1 mb-3 text-xs text-muted-foreground">
                    o seleccioná uno desde tu equipo
                </p>
                <input
                    ref={inputRef}
                    id={name}
                    name={name}
                    type="file"
                    className="sr-only"
                    tabIndex={-1}
                    accept={configuracion.extensiones
                        .map((extension) => `.${extension}`)
                        .join(',')}
                    disabled={disabled}
                    aria-describedby={`${ayudaId}${mensajeError ? ` ${errorId}` : ''}`}
                    onChange={(event) => {
                        seleccionar(Array.from(event.target.files ?? []));
                        event.target.value = '';
                    }}
                />
                <EureButtonSecondary
                    type="button"
                    onClick={() => inputRef.current?.click()}
                    disabled={disabled}
                    aria-describedby={`${ayudaId}${mensajeError ? ` ${errorId}` : ''}`}
                    aria-invalid={Boolean(mensajeError)}
                    autoFocus
                >
                    {value ? 'Cambiar archivo' : 'Seleccionar archivo'}
                </EureButtonSecondary>
            </div>
            {value && (
                <div className="mt-3 flex items-center gap-3 rounded-sm border border-border bg-muted/20 p-3">
                    <FileIcon
                        className="size-5 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <div className="min-w-0 flex-1 text-sm" role="status">
                        <p className="break-all font-medium">{value.name}</p>
                        <p className="text-xs text-muted-foreground">
                            {formatFileSize(value.size)}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        title="Quitar archivo seleccionado"
                        aria-label="Quitar archivo seleccionado"
                        disabled={disabled}
                        onClick={() => {
                            setErrorLocal(null);
                            onChange(null);
                        }}
                    >
                        <XIcon className="size-4" aria-hidden="true" />
                    </Button>
                </div>
            )}
            <p id={ayudaId} className="mt-2 text-xs text-muted-foreground">
                {configuracion.extensiones.join(', ').toUpperCase()}. Máximo{' '}
                {formatFileSize(configuracion.max_bytes)} por archivo.
            </p>
            {mensajeError && (
                <p
                    id={errorId}
                    role="alert"
                    className="mt-1 text-xs font-medium text-eure-error"
                >
                    {mensajeError}
                </p>
            )}
        </div>
    );
}
