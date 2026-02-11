import { cn } from '@/lib/utils';

interface ToggleProps {
    enabled: boolean;
    onChange: (enabled: boolean) => void;
    labelLeft?: string;
    labelRight?: string;
    className?: string;
}

function Toggle({ enabled, onChange, labelLeft, labelRight, className }: ToggleProps) {
    return (
        <div className={cn('inline-flex items-center gap-2', className)}>
            {labelLeft && (
                <span className={cn('text-body-sm', enabled ? 'text-surface-400' : 'text-surface-900 font-medium')}>
                    {labelLeft}
                </span>
            )}
            <button
                type="button"
                role="switch"
                aria-checked={enabled}
                onClick={() => onChange(!enabled)}
                className={cn(
                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-pill border-2 border-transparent transition-colors duration-normal focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2',
                    enabled ? 'bg-brand-600' : 'bg-surface-200',
                )}
            >
                <span
                    className={cn(
                        'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-soft transition-transform duration-normal',
                        enabled ? 'translate-x-5' : 'translate-x-0',
                    )}
                />
            </button>
            {labelRight && (
                <span className={cn('text-body-sm', enabled ? 'text-surface-900 font-medium' : 'text-surface-400')}>
                    {labelRight}
                </span>
            )}
        </div>
    );
}

export { Toggle, type ToggleProps };
