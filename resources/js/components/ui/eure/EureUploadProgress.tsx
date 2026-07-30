import { cn } from '@/lib/utils';
import { Spinner } from '@/components/ui/spinner';

const RADIUS = 20;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

interface EureUploadProgressProps {
    progress: number;
    fileName?: string;
    className?: string;
}

export default function EureUploadProgress({
    progress,
    fileName,
    className,
}: EureUploadProgressProps) {
    const isComplete = progress >= 100;
    const offset = CIRCUMFERENCE - (Math.min(progress, 100) / 100) * CIRCUMFERENCE;

    return (
        <div className={cn('flex flex-col items-center gap-2', className)}>
            {isComplete ? (
                <Spinner className="size-10" />
            ) : (
                <div className="relative flex items-center justify-center">
                    <svg viewBox="0 0 50 50" className="size-16 -rotate-90">
                        <circle
                            cx="25"
                            cy="25"
                            r={RADIUS}
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="4"
                            className="text-muted"
                        />
                        <circle
                            cx="25"
                            cy="25"
                            r={RADIUS}
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="4"
                            strokeLinecap="round"
                            className="text-[var(--eure-primary)] transition-all"
                            strokeDasharray={CIRCUMFERENCE}
                            strokeDashoffset={offset}
                        />
                    </svg>
                    <span className="absolute text-xs font-semibold text-[var(--eure-text)]">
                        {progress}%
                    </span>
                </div>
            )}
            {fileName && (
                <span className="max-w-xs truncate text-sm text-[var(--eure-sub)]">{fileName}</span>
            )}
        </div>
    );
}
