import { cn } from '@/lib/utils';
import { TextareaHTMLAttributes, forwardRef } from 'react';

interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    label?: string;
    error?: string;
}

const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(
    ({ className, label, error, id, ...props }, ref) => {
        const textareaId = id || label?.toLowerCase().replace(/\s+/g, '-');

        return (
            <div className="w-full">
                {label && (
                    <label htmlFor={textareaId} className="block text-body-sm font-medium text-surface-700 mb-1.5">
                        {label}
                    </label>
                )}
                <textarea
                    ref={ref}
                    id={textareaId}
                    className={cn(
                        'block w-full rounded-input border border-surface-200 bg-white px-3 py-2 text-body-sm text-surface-900 placeholder:text-surface-400 transition-all duration-fast',
                        'focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none',
                        'disabled:bg-surface-50 disabled:text-surface-500',
                        error && 'border-red-300 focus:border-red-500 focus:ring-red-500/20',
                        className,
                    )}
                    {...props}
                />
                {error && (
                    <p className="mt-1.5 text-caption text-red-600">{error}</p>
                )}
            </div>
        );
    },
);

Textarea.displayName = 'Textarea';
export { Textarea, type TextareaProps };
