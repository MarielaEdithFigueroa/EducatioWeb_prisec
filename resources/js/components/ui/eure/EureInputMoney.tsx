import { useEffect, useState } from 'react';
import {
    formatMoneyArgentino,
    parseMoneyInput,
    sanitizeMoneyInput,
} from '@/lib/money';
import { cn } from '@/lib/utils';
import EureInputGroup from './EureInputGroup';

interface EureInputMoneyProps {
    label: string;
    name: string;
    value: number;
    onChange: (value: number) => void;
    error?: boolean;
    errorMessage?: string;
    helperText?: string;
    className?: string;
    classNameInput?: string;
    classNameLabel?: string;
    required?: boolean;
}

export default function EureInputMoney({
    label,
    name,
    value,
    onChange,
    error = false,
    errorMessage = '',
    helperText = '',
    className = '',
    classNameInput = '',
    classNameLabel = '',
    required = false,
}: EureInputMoneyProps) {
    const [text, setText] = useState(() =>
        value ? formatMoneyArgentino(value) : '',
    );
    const [editing, setEditing] = useState(false);

    // Sync the displayed text from `value` whenever it changes from outside
    // this input (e.g. a "copy to all" action on a sibling field) — but not
    // while the user is actively typing here, or every keystroke would be
    // clobbered by the echoed prop update.
    useEffect(() => {
        if (!editing) {
            setText(value ? formatMoneyArgentino(value) : '');
        }
    }, [value, editing]);

    return (
        <EureInputGroup
            label={label}
            name={name}
            type="text"
            inputMode="decimal"
            placeholder="0,00"
            autoComplete="off"
            value={text}
            error={error}
            errorMessage={errorMessage}
            helperText={helperText}
            className={className}
            classNameInput={cn('text-right', classNameInput)}
            classNameLabel={classNameLabel}
            required={required}
            onChange={(e) => {
                const sanitized = sanitizeMoneyInput(e.target.value);
                setText(sanitized);
                onChange(parseMoneyInput(sanitized));
            }}
            onBlur={() => {
                setEditing(false);
                setText(value ? formatMoneyArgentino(value) : '');
            }}
            onFocus={(e) => {
                setEditing(true);
                e.target.select();
            }}
        />
    );
}
