import { cn } from '@/lib/utils';
import { ButtonHTMLAttributes, forwardRef } from 'react';
import { Loader2 } from 'lucide-react';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'primary' | 'secondary' | 'ghost' | 'danger' | 'outline';
    size?: 'sm' | 'md' | 'lg';
    loading?: boolean;
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
    ({ className, variant = 'primary', size = 'md', loading, disabled, children, ...props }, ref) => {
        return (
            <button
                ref={ref}
                disabled={disabled || loading}
                className={cn(
                    'inline-flex items-center justify-center gap-2 font-medium transition-all duration-normal focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
                    {
                        'bg-brand-600 text-white hover:bg-brand-700 active:bg-brand-800 shadow-soft hover:shadow-medium': variant === 'primary',
                        'bg-surface-100 text-surface-700 hover:bg-surface-200 active:bg-surface-300': variant === 'secondary',
                        'text-surface-600 hover:bg-surface-100 hover:text-surface-900': variant === 'ghost',
                        'bg-red-600 text-white hover:bg-red-700 active:bg-red-800': variant === 'danger',
                        'border border-surface-200 bg-white text-surface-700 hover:bg-surface-50 hover:border-surface-300': variant === 'outline',
                    },
                    {
                        'h-8 px-3 text-body-sm rounded-button': size === 'sm',
                        'h-10 px-4 text-body-sm rounded-button': size === 'md',
                        'h-12 px-6 text-body rounded-button': size === 'lg',
                    },
                    className,
                )}
                {...props}
            >
                {loading && <Loader2 className="h-4 w-4 animate-spin" />}
                {children}
            </button>
        );
    },
);

Button.displayName = 'Button';
export { Button, type ButtonProps };
