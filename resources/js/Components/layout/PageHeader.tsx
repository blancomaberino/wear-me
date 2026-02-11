import { cn } from '@/lib/utils';
import { ReactNode } from 'react';

interface PageHeaderProps {
    title: string;
    description?: string;
    actions?: ReactNode;
    className?: string;
}

function PageHeader({ title, description, actions, className }: PageHeaderProps) {
    return (
        <div className={cn('flex items-start justify-between gap-4 mb-6', className)}>
            <div>
                <h1 className="text-heading-lg text-surface-900">{title}</h1>
                {description && (
                    <p className="mt-1 text-body-sm text-surface-500">{description}</p>
                )}
            </div>
            {actions && <div className="flex items-center gap-3 shrink-0">{actions}</div>}
        </div>
    );
}

export { PageHeader, type PageHeaderProps };
