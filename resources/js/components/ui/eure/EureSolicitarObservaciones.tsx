import { useState } from 'react';
import type { FormEvent } from 'react';

import EureTextarea from './EureTextarea';

export type SolicitarObservacionesCloseReason = 'cancel' | 'close' | 'backdrop';

interface EureSolicitarObservacionesProps {
    open: boolean;
    title: string;
    confirmLabel?: string;
    cancelLabel?: string;
    placeholder?: string;
    loading?: boolean;
    error?: string | null;
    minLen?: number;
    onConfirm: (motivo: string) => void;
    onClose: (reason: SolicitarObservacionesCloseReason) => void;
}

export default function EureSolicitarObservaciones(props: EureSolicitarObservacionesProps) {
    if (!props.open) {
        return null;
    }

    return <EureSolicitarObservacionesForm {...props} />;
}

function EureSolicitarObservacionesForm({
    title,
    confirmLabel = 'Confirmar',
    cancelLabel = 'Cancelar',
    placeholder = 'Motivo...',
    loading = false,
    error = null,
    minLen = 3,
    onConfirm,
    onClose,
}: EureSolicitarObservacionesProps) {
    const [motivo, setMotivo] = useState('');
    const [localError, setLocalError] = useState<string | null>(null);

    function submit(e: FormEvent) {
        e.preventDefault();

        const m = motivo.trim();

        if (m.length < minLen) {
            setLocalError(`Ingresá un motivo (mínimo ${minLen} caracteres).`);

            return;
        }

        setLocalError(null);
        onConfirm(m);
    }

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 transition-opacity duration-200"
            role="presentation"
            onMouseDown={(e) => {
                if (e.target === e.currentTarget) {
                    onClose('backdrop');
                }
            }}
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
                className="animate-in zoom-in-95 fade-in w-[32.5rem] max-w-[92vw] rounded-xl bg-white p-6 shadow transition duration-200 ease-out"
            >
                <div className="flex items-start justify-between gap-4">
                    <h2
                        id="modal-title"
                        className="text-xl font-bold text-[var(--eure-text)]"
                    >
                        {title}
                    </h2>

                    <button
                        type="button"
                        className="rounded-md p-1 transition-colors disabled:opacity-50 text-[var(--eure-sub)] hover:bg-[var(--eure-bg)] hover:text-[var(--eure-text)]"
                        onClick={() => onClose('close')}
                        disabled={loading}
                        aria-label="Cerrar"
                    >
                        ✕
                    </button>
                </div>

                <form onSubmit={submit} className="mt-4 space-y-3">
                    <EureTextarea
                        rows={4}
                        placeholder={placeholder}
                        value={motivo}
                        onChange={(e) => setMotivo(e.target.value)}
                        disabled={loading}
                    />

                    {(error || localError) && (
                        <div className="rounded-md border px-4 py-2 text-sm border-[var(--eure-error-border)] bg-[var(--eure-error-bg)] text-[var(--eure-error)]">
                            {error || localError}
                        </div>
                    )}

                    <div className="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            className="rounded-md border px-4 py-2 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50 border-[var(--eure-border)] text-[var(--eure-text)] bg-white hover:bg-[var(--eure-bg)]"
                            onClick={() => onClose('cancel')}
                            disabled={loading}
                        >
                            {cancelLabel}
                        </button>

                        <button
                            type="submit"
                            className="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50 bg-[var(--eure-error-border)] hover:bg-[var(--eure-error)]"
                            disabled={loading}
                        >
                            {loading && (
                                <svg className="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                            )}
                            {confirmLabel}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
