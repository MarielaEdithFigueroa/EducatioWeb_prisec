import { format, isValid, parse } from 'date-fns';
import { es } from 'date-fns/locale';
import { CalendarDaysIcon, CircleAlertIcon } from 'lucide-react';
import React, { useRef, useState } from 'react';
import ReactDatePicker, { registerLocale } from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

import { cn } from '@/lib/utils';

registerLocale('es', es);

type ViewMode = 'calendar' | 'month' | 'year';

export interface EureDatePickerProps {
    value: Date | null;
    onChange: (date: Date | null) => void;
    format?: string;
    placeholder?: string;
    minDate?: Date;
    maxDate?: Date;
    error?: boolean;
    disabled?: boolean;
    className?: string;
    onKeyDown?: (e: React.KeyboardEvent<HTMLInputElement>) => void;
}

interface CustomInputProps {
    value?: string;
    onClick?: () => void;
    onChange?: React.ChangeEventHandler<HTMLInputElement>;
    onKeyDown?: (e: React.KeyboardEvent<HTMLInputElement>) => void;
    placeholder?: string;
    error?: boolean;
    disabled?: boolean;
}

const applyDateMask = (value: string): string => {
    const digits = value.replace(/\D/g, '').slice(0, 8);

    if (digits.length <= 2) {
        return digits;
    }

    if (digits.length <= 4) {
        return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    }

    return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
};

const CustomInput = React.forwardRef<HTMLInputElement, CustomInputProps>(
    ({ value, onClick, onChange, onKeyDown, placeholder, error = false, disabled = false }, ref) => {
        const [localValue, setLocalValue] = useState(value ?? '');
        const [syncedValue, setSyncedValue] = useState(value);

        if (syncedValue !== value) {
            setSyncedValue(value);
            setLocalValue(value ?? '');
        }

        const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
            const masked = applyDateMask(e.target.value);
            setLocalValue(masked);
            onChange?.(e);
        };

        return (
            <div className="relative w-full">
                <input
                    ref={ref}
                    value={localValue}
                    onChange={handleChange}
                    onKeyDown={onKeyDown}
                    placeholder={placeholder}
                    disabled={disabled}
                    aria-invalid={error || undefined}
                    className={cn(
                        'w-full rounded-md border px-3 py-2 pr-9 text-sm outline-none transition-colors',
                        'border-[var(--eure-border)] bg-background text-[var(--eure-text)]',
                        'placeholder:text-[var(--eure-sub)]',
                        'focus:border-[var(--eure-primary)] focus:ring-2 focus:ring-[var(--eure-primary)]',
                        error && 'border-[var(--eure-error-border)] focus:ring-[var(--eure-error)]',
                        disabled && 'cursor-not-allowed opacity-50',
                    )}
                />
                <button
                    type="button"
                    onClick={onClick}
                    disabled={disabled}
                    tabIndex={-1}
                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none disabled:pointer-events-none disabled:opacity-40"
                >
                    <CalendarDaysIcon className="h-4 w-4" aria-hidden="true" />
                </button>
                {error && (
                    <CircleAlertIcon
                        className="absolute top-1/2 right-8 size-4 -translate-y-1/2 text-[var(--eure-error)]"
                        aria-hidden="true"
                    />
                )}
            </div>
        );
    },
);

CustomInput.displayName = 'CustomInput';

export default function EureDatePicker({
    value,
    onChange,
    format: dateFormat = 'dd/MM/yyyy',
    placeholder = 'DD/MM/AAAA',
    minDate,
    maxDate,
    error = false,
    disabled = false,
    className = '',
    onKeyDown,
}: EureDatePickerProps) {
    const [viewMode, setViewMode] = useState<ViewMode>('calendar');
    const [viewDate, setViewDate] = useState<Date>(value ?? new Date());
    const isTypingRef = useRef(false);

    const handleChangeRaw = (
        event?: React.MouseEvent<HTMLElement> | React.KeyboardEvent<HTMLElement>,
        selectionMeta?: { date: Date; formattedDate: string },
    ) => {
        if (selectionMeta) {
            return;
        }

        isTypingRef.current = true;

        const raw = (event?.target as HTMLInputElement | undefined)?.value ?? '';

        if (!raw) {
            isTypingRef.current = false;
            event?.preventDefault();
            onChange(null);

            return;
        }

        const parsed = parse(raw, dateFormat, new Date());
        const isComplete = isValid(parsed) && format(parsed, dateFormat) === raw;

        if (isComplete) {
            isTypingRef.current = false;
            onChange(parsed);
            setViewDate(parsed);
        } else {
            event?.preventDefault();
        }
    };

    const handleChange = (date: Date | null) => {
        if (date === null && isTypingRef.current) {
            return;
        }

        if (viewMode === 'year') {
            if (date) {
                setViewDate(date);
            }

            setViewMode('calendar');

            return;
        }

        if (viewMode === 'month') {
            if (date) {
                setViewDate(date);
            }

            setViewMode('calendar');

            return;
        }

        onChange(date);

        if (date) {
            setViewDate(date);
        }
    };

    const handleCalendarClose = () => {
        setViewMode('calendar');
        isTypingRef.current = false;
    };

    return (
        <div className={`relative w-full ${className}`}>
            <ReactDatePicker
                selected={value}
                onChange={handleChange}
                onChangeRaw={handleChangeRaw}
                onCalendarClose={handleCalendarClose}
                onMonthChange={(d) => setViewDate(d)}
                openToDate={viewDate}
                shouldCloseOnSelect={viewMode === 'calendar'}
                dateFormat={dateFormat}
                locale="es"
                placeholderText={placeholder}
                minDate={minDate}
                maxDate={maxDate}
                disabled={disabled}
                showYearPicker={viewMode === 'year'}
                showMonthYearPicker={viewMode === 'month'}
                customInput={
                    <CustomInput error={error} disabled={disabled} onKeyDown={onKeyDown} />
                }
                {...(viewMode === 'calendar' && {
                    renderCustomHeader: ({
                        date,
                        decreaseMonth,
                        increaseMonth,
                        prevMonthButtonDisabled,
                        nextMonthButtonDisabled,
                    }: Parameters<NonNullable<React.ComponentProps<typeof ReactDatePicker>['renderCustomHeader']>>[0]) => (
                        <div className="flex items-center justify-between px-2 pb-2">
                            <button
                                type="button"
                                className="flex h-7 w-7 items-center justify-center rounded text-base text-[var(--eure-sub)] transition-all duration-150 hover:bg-[var(--accent)] hover:text-[var(--eure-text)] disabled:cursor-not-allowed disabled:opacity-30"
                                onClick={decreaseMonth}
                                disabled={prevMonthButtonDisabled}
                            >
                                ‹
                            </button>
                            <div className="flex gap-1">
                                <button
                                    type="button"
                                    className="rounded px-1 text-sm font-semibold capitalize text-[var(--eure-text)] transition-colors duration-150 hover:bg-[var(--accent)]"
                                    onClick={() => setViewMode('month')}
                                >
                                    {format(date, 'MMMM', { locale: es })}
                                </button>
                                <button
                                    type="button"
                                    className="rounded px-1 text-sm font-semibold capitalize text-[var(--eure-text)] transition-colors duration-150 hover:bg-[var(--accent)]"
                                    onClick={() => setViewMode('year')}
                                >
                                    {format(date, 'yyyy')}
                                </button>
                            </div>
                            <button
                                type="button"
                                className="flex h-7 w-7 items-center justify-center rounded text-base text-[var(--eure-sub)] transition-all duration-150 hover:bg-[var(--accent)] hover:text-[var(--eure-text)] disabled:cursor-not-allowed disabled:opacity-30"
                                onClick={increaseMonth}
                                disabled={nextMonthButtonDisabled}
                            >
                                ›
                            </button>
                        </div>
                    ),
                })}
                popperClassName="z-[9999]"
                calendarClassName="eure-datepicker"
                popperProps={{ strategy: 'fixed' }}
            />
        </div>
    );
}
