import { useState } from 'react';
import { diffForHumans, formatDateTime } from '@/lib/date';

interface RelativeDateToggleProps {
    value: string | Date | null | undefined;
    emptyText?: string;
    className?: string;
}

export default function RelativeDateToggle({
    value,
    emptyText = 'Sin datos',
    className = '',
}: RelativeDateToggleProps) {
    const [showExact, setShowExact] = useState(false);
    const exact = formatDateTime(value, emptyText);

    if (!value || exact === emptyText) {
        return <span className={className}>{emptyText}</span>;
    }

    return (
        <button
            type="button"
            className={`cursor-pointer underline-offset-2 hover:underline ${showExact ? 'not-italic' : 'italic'} ${className}`}
            title={exact}
            onClick={() => setShowExact((current) => !current)}
        >
            {showExact ? exact : diffForHumans(value, emptyText)}
        </button>
    );
}
