import { cn } from '@/lib/utils';
import { LucideIcon } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface StatCardProps {
    label: string;
    value: number | string;
    icon: LucideIcon;
    href?: string;
    iconColor?: string;
    iconBg?: string;
    className?: string;
}

function StatCard({ label, value, icon: Icon, href, iconColor = 'text-brand-600', iconBg = 'bg-brand-50', className }: StatCardProps) {
    const content = (
        <div className={cn('rounded-card bg-white border border-surface-200 p-5 shadow-xs', href && 'hover:shadow-soft hover:-translate-y-0.5 transition-all duration-normal', className)}>
            <div className="flex items-center gap-4">
                <div className={cn('flex items-center justify-center rounded-xl h-12 w-12', iconBg)}>
                    <Icon className={cn('h-6 w-6', iconColor)} />
                </div>
                <div>
                    <p className="text-heading-lg text-surface-900">{value}</p>
                    <p className="text-body-sm text-surface-500">{label}</p>
                </div>
            </div>
        </div>
    );

    if (href) {
        return <Link href={href}>{content}</Link>;
    }

    return content;
}

export { StatCard, type StatCardProps };
