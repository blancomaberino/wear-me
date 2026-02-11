import { cn } from '@/lib/utils';
import { SelectHTMLAttributes, forwardRef } from 'react';

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
    label?: string;
    error?: string;
    options: { value: string; label: string }[];
    placeholder?: string;
}

const Select = forwardRef<HTMLSelectElement, SelectProps>(
    ({ className, label, error, options, placeholder, id, ...props }, ref) => {
        const selectId = id || label?.toLowerCase().replace(/\s+/g, '-');

        return (
            <div className="w-full">
                {label && (
                    <label htmlFor={selectId} className="block text-body-sm font-medium text-surface-700 mb-1.5">
                        {label}
                    </label>
                )}
                <select
                    ref={ref}
                    id={selectId}
                    className={cn(
                        'block w-full rounded-input border border-surface-200 bg-white px-3 py-2 text-body-sm text-surface-900 transition-all duration-fast',
                        'focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none',
                        error && 'border-red-300 focus:border-red-500 focus:ring-red-500/20',
                        className,
                    )}
                    {...props}
                >
                    {placeholder && <option value="">{placeholder}</option>}
                    {options.map((opt) => (
                        <option key={opt.value} value={opt.value}>{opt.label}</option>
                    ))}
                </select>
                {error && (
                    <p className="mt-1.5 text-caption text-red-600">{error}</p>
                )}
            </div>
        );
    },
);

Select.displayName = 'Select';
export { Select, type SelectProps };
