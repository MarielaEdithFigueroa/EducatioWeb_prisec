import { useId } from 'react';
import type { SingleValue } from 'react-select';
import Select from 'react-select';

import { eureSelectStyles } from './select-styles';

export interface EureSelectOption {
    value: string | number;
    label: string;
}

interface EureSelectProps {
    options: EureSelectOption[];
    value: EureSelectOption | null;
    onChange: (selected: EureSelectOption | null) => void;
    placeholder?: string;
    isDisabled?: boolean;
    error?: boolean;
    className?: string;
    isClearable?: boolean;
}

export default function EureSelect({
    options,
    value,
    onChange,
    placeholder = 'Seleccione una opción',
    isDisabled = false,
    error = false,
    className,
    isClearable = false,
}: EureSelectProps) {
    // react-select otherwise numbers its generated ids from a module-level
    // mount counter, which drifts between the SSR pass and client hydration
    // (or across Inertia client-side navigations) and triggers React
    // hydration mismatches. useId() is tree-position-based, so it's
    // guaranteed to match between server and client.
    const instanceId = useId();

    return (
        <div className={className}>
            <Select<EureSelectOption, false>
                instanceId={instanceId}
                value={value}
                onChange={(newValue: SingleValue<EureSelectOption>) => onChange(newValue)}
                options={options}
                placeholder={placeholder}
                styles={eureSelectStyles<EureSelectOption, false>(error)}
                isDisabled={isDisabled}
                isClearable={isClearable}
            />
        </div>
    );
}
