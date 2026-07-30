import type { StylesConfig } from 'react-select';

export function eureSelectStyles<T, IsMulti extends boolean = false>(
    error: boolean = false,
): StylesConfig<T, IsMulti> {
    return {
        control: (base, state) => ({
            ...base,
            borderColor: error
                ? 'var(--eure-error-border)'
                : state.isFocused
                  ? 'var(--eure-border)'
                  : 'var(--eure-border)',
            boxShadow: state.isFocused ? '0 0 0 1px var(--color-primary)' : 'none',
            '&:hover': {
                borderColor: state.isFocused ? 'var(--eure-border)' : 'var(--eure-border)',
            },
            borderRadius: '0.5rem',
            backgroundColor: 'var(--background)',
        }),
        placeholder: (base) => ({
            ...base,
            color: 'var(--eure-sub)',
        }),
        menu: (base) => ({
            ...base,
            backgroundColor: 'var(--background)',
            borderColor: 'var(--eure-border)',
            zIndex: 50,
        }),
        option: (base, state) => ({
            ...base,
            backgroundColor: state.isSelected
                ? 'var(--eure-primary)'
                : state.isFocused
                  ? 'var(--eure-table-row-hover)'
                  : 'transparent',
            color: state.isSelected ? 'white' : 'var(--eure-text)',
        }),
        multiValue: (base) => ({
            ...base,
            backgroundColor: 'var(--eure-bg)',
        }),
        multiValueLabel: (base) => ({
            ...base,
            color: 'var(--eure-text)',
        }),
        multiValueRemove: (base) => ({
            ...base,
            color: 'var(--eure-text)',
            ':hover': {
                backgroundColor: 'var(--eure-error)',
                color: 'white',
            },
        }),
        singleValue: (base) => ({
            ...base,
            color: 'var(--eure-text)',
        }),
        input: (base) => ({
            ...base,
            color: 'var(--eure-text)',
        }),
    };
}
