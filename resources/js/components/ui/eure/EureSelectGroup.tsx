import type { EureSelectOption } from './EureSelect';
import EureSelect from './EureSelect';

export type { EureSelectOption };

interface EureSelectGroupProps {
    label: string;
    name: string;
    value: EureSelectOption | null;
    onChange: (value: EureSelectOption | null) => void;
    options: EureSelectOption[];
    placeholder?: string;
    error?: boolean;
    errorMessage?: string;
    helperText?: string;
    className?: string;
    isDisabled?: boolean;
    required?: boolean;
}

export default function EureSelectGroup({
    label,
    name,
    value,
    onChange,
    options,
    placeholder,
    error = false,
    errorMessage = '',
    helperText = '',
    className = '',
    isDisabled = false,
    required = false,
}: EureSelectGroupProps) {
    return (
        <div className={className}>
            <label
                htmlFor={name}
                className="block text-sm font-semibold tracking-wide mb-1.5 text-[var(--eure-text)]"
            >
                {label}
            </label>

            <EureSelect
                value={value}
                onChange={onChange}
                options={options}
                placeholder={placeholder}
                error={error}
                className="w-full"
                isDisabled={isDisabled}
            />

            {errorMessage && error ? (
                <p className="mt-1 text-xs font-medium text-[var(--eure-error)]">{errorMessage}</p>
            ) : helperText ? (
                <p className="mt-1 text-xs italic text-[var(--eure-sub)]">{helperText}</p>
            ) : null}
        </div>
    );
}
