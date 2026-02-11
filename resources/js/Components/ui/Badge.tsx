import { cn } from '@/lib/utils';
import { HTMLAttributes } from 'react';

interface BadgeProps extends HTMLAttributes<HTMLSpanElement> {
    variant?: 'brand' | 'success' | 'warning' | 'danger' | 'neutral';
    size?: 'sm' | 'md';
}

function Badge({ className, variant = 'neutral', size = 'sm', ...props }: BadgeProps) {
    return (
        <span
            className={cn(
                'inline-flex items-center font-medium rounded-badge',
                {
                    'bg-brand-50 text-brand-700': variant === 'brand',
                    'bg-emerald-50 text-emerald-700': variant === 'success',
                    'bg-amber-50 text-amber-700': variant === 'warning',
                    'bg-red-50 text-red-700': variant === 'danger',
                    'bg-surface-100 text-surface-600': variant === 'neutral',
                },
                {
                    'px-2 py-0.5 text-caption': size === 'sm',
                    'px-2.5 py-1 text-body-sm': size === 'md',
                },
                className,
            )}
            {...props}
        />
    );
}

export { Badge, type BadgeProps };
