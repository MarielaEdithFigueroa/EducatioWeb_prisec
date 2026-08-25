import EureDatePicker from './EureDatePicker';
import type { EureDatePickerProps } from './EureDatePicker';

interface EureDatePickerGroupProps extends EureDatePickerProps {
    label: string;
    name: string;
    errorMessage?: string;
    helperText?: string;
    required?: boolean;
    classNameWrapper?: string;
}

export default function EureDatePickerGroup({
    label,
    name,
    error = false,
    errorMessage = '',
    helperText = '',
    required = false,
    classNameWrapper = '',
    ...props
}: EureDatePickerGroupProps) {
    return (
        <div className={classNameWrapper}>
            <label
                htmlFor={name}
                className="mb-1.5 block text-sm font-semibold tracking-wide text-[var(--eure-text)]"
            >
                {label}
                {required && <span aria-hidden="true"> *</span>}
            </label>

            <EureDatePicker id={name} name={name} error={error} {...props} />

            {errorMessage && error ? (
                <p className="mt-1 text-xs font-medium text-[var(--eure-error)]">
                    {errorMessage}
                </p>
            ) : helperText ? (
                <p className="mt-1 text-xs italic text-[var(--eure-sub)]">
                    {helperText}
                </p>
            ) : null}
        </div>
    );
}
