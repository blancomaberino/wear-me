import { cn } from '@/lib/utils';
import { HTMLAttributes } from 'react';

interface CardProps extends HTMLAttributes<HTMLDivElement> {
    variant?: 'default' | 'elevated' | 'interactive';
}

function Card({ className, variant = 'default', ...props }: CardProps) {
    return (
        <div
            className={cn(
                'rounded-card bg-white border border-surface-200',
                {
                    'shadow-xs': variant === 'default',
                    'shadow-soft': variant === 'elevated',
                    'shadow-xs hover:shadow-medium hover:-translate-y-0.5 transition-all duration-normal cursor-pointer': variant === 'interactive',
                },
                className,
            )}
            {...props}
        />
    );
}

function CardHeader({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('px-6 py-4 border-b border-surface-100', className)} {...props} />;
}

function CardBody({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('px-6 py-4', className)} {...props} />;
}

function CardFooter({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('px-6 py-4 border-t border-surface-100', className)} {...props} />;
}

export { Card, CardHeader, CardBody, CardFooter, type CardProps };
