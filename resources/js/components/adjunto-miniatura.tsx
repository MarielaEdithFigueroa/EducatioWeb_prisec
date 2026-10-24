import {
    FileIcon,
    FileImageIcon,
    FileSpreadsheetIcon,
    FileTextIcon,
} from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';
import type { Adjunto } from '@/types/adjuntos';

type Props = {
    documento: Pick<Adjunto, 'nombre_original' | 'mime' | 'activo'>;
    src: string;
    className?: string;
};

export default function AdjuntoMiniatura({ documento, src, className }: Props) {
    const [imagenFallida, setImagenFallida] = useState<string | null>(null);
    const extension = documento.nombre_original.split('.').pop()?.toLowerCase();
    const esImagen = ['image/jpeg', 'image/png', 'image/webp'].includes(
        documento.mime,
    );
    const esPdf = extension === 'pdf';
    const esPlanilla = ['xls', 'xlsx', 'ods', 'csv'].includes(extension ?? '');
    const esTexto = ['doc', 'docx', 'odt', 'txt', 'rtf'].includes(
        extension ?? '',
    );
    const Icono = esImagen
        ? FileImageIcon
        : esPlanilla
          ? FileSpreadsheetIcon
          : esPdf || esTexto
            ? FileTextIcon
            : FileIcon;

    return (
        <div
            className={cn(
                'flex h-36 items-center justify-center overflow-hidden rounded-sm bg-muted/40',
                className,
            )}
        >
            {esImagen && documento.activo && imagenFallida !== src ? (
                <img
                    src={src}
                    alt={`Vista previa de ${documento.nombre_original}`}
                    loading="lazy"
                    decoding="async"
                    className="size-full object-contain"
                    onError={() => setImagenFallida(src)}
                />
            ) : (
                <div className="flex flex-col items-center gap-2 text-muted-foreground">
                    <Icono
                        className={cn('size-10', {
                            'text-red-700 dark:text-red-400': esPdf,
                            'text-emerald-700 dark:text-emerald-400':
                                esPlanilla,
                            'text-blue-700 dark:text-blue-400': esTexto,
                        })}
                        aria-hidden="true"
                    />
                    <span className="max-w-36 truncate text-xs font-medium uppercase">
                        {extension || 'Archivo'}
                    </span>
                </div>
            )}
        </div>
    );
}
