import React, { useId } from 'react';
import type { MultiValue, OptionProps, StylesConfig, ValueContainerProps } from 'react-select';
import Select, { components } from 'react-select';

import type { EureSelectOption } from './EureSelect';

export interface EureMultiSelectProps {
    value: EureSelectOption[];
    onChange: (values: EureSelectOption[]) => void;
    options: EureSelectOption[];
    placeholder?: string;
    error?: boolean;
    className?: string;
    isDisabled?: boolean;
}

function CustomOption(props: OptionProps<EureSelectOption, true>) {
    const { isSelected, label, innerProps, isFocused } = props;

    return (
        <div
            {...innerProps}
            style={{
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                padding: '6px 12px',
                cursor: 'pointer',
                backgroundColor: isFocused ? 'var(--accent)' : 'transparent',
            }}
        >
            <input
                type="checkbox"
                checked={isSelected}
                onChange={() => {}}
                style={{ pointerEvents: 'none', accentColor: 'var(--color-primary)' }}
            />
            <span style={{ fontSize: '0.875rem' }}>{label}</span>
        </div>
    );
}

function CustomValueContainer(props: ValueContainerProps<EureSelectOption, true>) {
    const { children, getValue, selectProps } = props;
    const selected = getValue();

    if (selected.length === 0) {
        return <components.ValueContainer {...props}>{children}</components.ValueContainer>;
    }

    const childrenArray = React.Children.toArray(children);
    const inputChild = childrenArray[childrenArray.length - 1];
    const countLabel = selected.length === 1 ? '1 seleccionada' : `${selected.length} seleccionadas`;

    return (
        <components.ValueContainer {...props}>
            {!selectProps.inputValue && <span style={{ fontSize: '0.875rem' }}>{countLabel}</span>}
            {inputChild}
        </components.ValueContainer>
    );
}

export default function EureMultiSelect({
    value,
    onChange,
    options,
    placeholder = 'Seleccione opciones',
    error = false,
    className = '',
    isDisabled = false,
}: EureMultiSelectProps) {
    // See EureSelect: useId() avoids react-select's mount-counter-based ids
    // drifting between SSR and client hydration.
    const instanceId = useId();

    const customStyles: StylesConfig<EureSelectOption, true> = {
        control: (base, state) => ({
            ...base,
            borderColor: error
                ? 'var(--eure-error-border)'
                : state.isFocused
                  ? 'var(--eure-border)'
                  : base.borderColor,
            boxShadow: state.isFocused ? '0 0 0 1px var(--color-primary)' : 'none',
            '&:hover': {
                borderColor: state.isFocused ? 'var(--eure-border)' : base.borderColor,
            },
            borderRadius: '0.5rem',
        }),
        placeholder: (base) => ({
            ...base,
            color: 'var(--eure-sub)',
        }),
        option: () => ({}),
    };

    return (
        <div className={className}>
            <Select<EureSelectOption, true>
                instanceId={instanceId}
                isMulti
                value={value}
                onChange={(newValue: MultiValue<EureSelectOption>) => onChange([...newValue])}
                options={options}
                placeholder={placeholder}
                styles={customStyles}
                isDisabled={isDisabled}
                closeMenuOnSelect={false}
                hideSelectedOptions={false}
                noOptionsMessage={() => 'Sin resultados'}
                components={{ Option: CustomOption, ValueContainer: CustomValueContainer }}
            />
        </div>
    );
}
