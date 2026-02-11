import { cn } from '@/lib/utils';
import { LucideIcon } from 'lucide-react';
import { ReactNode } from 'react';

interface EmptyStateProps {
    icon: LucideIcon;
    title: string;
    description?: string;
    action?: ReactNode;
    className?: string;
}

function EmptyState({ icon: Icon, title, description, action, className }: EmptyStateProps) {
    return (
        <div className={cn('flex flex-col items-center justify-center py-12 text-center', className)}>
            <div className="rounded-full bg-surface-100 p-4 mb-4">
                <Icon className="h-8 w-8 text-surface-400" />
            </div>
            <h3 className="text-heading-sm text-surface-900 mb-1">{title}</h3>
            {description && (
                <p className="text-body-sm text-surface-500 max-w-sm mb-4">{description}</p>
            )}
            {action}
        </div>
    );
}

export { EmptyState, type EmptyStateProps };
