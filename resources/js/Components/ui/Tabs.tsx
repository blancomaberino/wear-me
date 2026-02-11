import { cn } from '@/lib/utils';

interface Tab {
    id: string;
    label: string;
    count?: number;
}

interface TabsProps {
    tabs: Tab[];
    activeTab: string;
    onChange: (tabId: string) => void;
    variant?: 'pill' | 'underline';
    className?: string;
}

function Tabs({ tabs, activeTab, onChange, variant = 'pill', className }: TabsProps) {
    if (variant === 'underline') {
        return (
            <div className={cn('border-b border-surface-200', className)}>
                <nav className="flex gap-0 -mb-px" role="tablist">
                    {tabs.map((tab) => (
                        <button
                            key={tab.id}
                            role="tab"
                            aria-selected={activeTab === tab.id}
                            onClick={() => onChange(tab.id)}
                            className={cn(
                                'px-4 py-2.5 text-body-sm font-medium border-b-2 transition-colors duration-fast whitespace-nowrap',
                                activeTab === tab.id
                                    ? 'border-brand-500 text-brand-600'
                                    : 'border-transparent text-surface-500 hover:text-surface-700 hover:border-surface-300',
                            )}
                        >
                            {tab.label}
                            {tab.count !== undefined && (
                                <span className={cn(
                                    'ml-2 rounded-pill px-2 py-0.5 text-caption',
                                    activeTab === tab.id ? 'bg-brand-50 text-brand-600' : 'bg-surface-100 text-surface-500',
                                )}>
                                    {tab.count}
                                </span>
                            )}
                        </button>
                    ))}
                </nav>
            </div>
        );
    }

    return (
        <div className={cn('inline-flex gap-1 rounded-xl bg-surface-100 p-1', className)} role="tablist">
            {tabs.map((tab) => (
                <button
                    key={tab.id}
                    role="tab"
                    aria-selected={activeTab === tab.id}
                    onClick={() => onChange(tab.id)}
                    className={cn(
                        'rounded-lg px-3 py-1.5 text-body-sm font-medium transition-all duration-fast whitespace-nowrap',
                        activeTab === tab.id
                            ? 'bg-white text-surface-900 shadow-xs'
                            : 'text-surface-500 hover:text-surface-700',
                    )}
                >
                    {tab.label}
                    {tab.count !== undefined && (
                        <span className="ml-1.5 text-caption text-surface-400">
                            {tab.count}
                        </span>
                    )}
                </button>
            ))}
        </div>
    );
}

export { Tabs, type TabsProps, type Tab };
