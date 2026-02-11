import { cn } from '@/lib/utils';
import { InputHTMLAttributes, forwardRef } from 'react';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    description?: string;
    suffix?: string;
}

const Input = forwardRef<HTMLInputElement, InputProps>(
    ({ className, label, error, description, suffix, id, ...props }, ref) => {
        const inputId = id || label?.toLowerCase().replace(/\s+/g, '-');

        return (
            <div className="w-full">
                {label && (
                    <label htmlFor={inputId} className="block text-body-sm font-medium text-surface-700 mb-1.5">
                        {label}
                    </label>
                )}
                {description && (
                    <p className="text-caption text-surface-500 mb-1.5">{description}</p>
                )}
                <div className="relative">
                    <input
                        ref={ref}
                        id={inputId}
                        className={cn(
                            'block w-full rounded-input border border-surface-200 bg-white px-3 py-2 text-body-sm text-surface-900 placeholder:text-surface-400 transition-all duration-fast',
                            'focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none',
                            'disabled:bg-surface-50 disabled:text-surface-500 disabled:cursor-not-allowed',
                            error && 'border-red-300 focus:border-red-500 focus:ring-red-500/20',
                            suffix && 'pr-12',
                            className,
                        )}
                        {...props}
                    />
                    {suffix && (
                        <span className="absolute right-3 top-1/2 -translate-y-1/2 text-body-sm text-surface-400 pointer-events-none">
                            {suffix}
                        </span>
                    )}
                </div>
                {error && (
                    <p className="mt-1.5 text-caption text-red-600">{error}</p>
                )}
            </div>
        );
    },
);

Input.displayName = 'Input';
export { Input, type InputProps };
