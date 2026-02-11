import { cn } from '@/lib/utils';

interface ProgressBarProps {
    value: number;
    max?: number;
    label?: string;
    showValue?: boolean;
    color?: 'brand' | 'emerald' | 'amber' | 'rose' | 'sky';
    className?: string;
}

function ProgressBar({ value, max = 100, label, showValue, color = 'brand', className }: ProgressBarProps) {
    const percentage = Math.min(100, Math.max(0, (value / max) * 100));

    const colorClasses = {
        brand: 'bg-brand-500',
        emerald: 'bg-emerald-500',
        amber: 'bg-amber-500',
        rose: 'bg-rose-500',
        sky: 'bg-sky-500',
    };

    return (
        <div className={cn('w-full', className)}>
            {(label || showValue) && (
                <div className="flex items-center justify-between mb-1.5">
                    {label && <span className="text-body-sm font-medium text-surface-700">{label}</span>}
                    {showValue && <span className="text-caption text-surface-500">{Math.round(percentage)}%</span>}
                </div>
            )}
            <div className="h-2 w-full rounded-pill bg-surface-100 overflow-hidden">
                <div
                    className={cn('h-full rounded-pill transition-all duration-slow', colorClasses[color])}
                    style={{ width: `${percentage}%` }}
                />
            </div>
        </div>
    );
}

export { ProgressBar, type ProgressBarProps };
