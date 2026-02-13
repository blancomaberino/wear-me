import { cn } from '@/lib/utils';
import { computeFitScore, FitLabel } from '@/lib/fitScore';
import { useTranslation } from 'react-i18next';
import { User, Garment } from '@/types';

interface FitBadgeProps {
    user: User;
    garment: Garment;
    className?: string;
}

const fitStyles: Record<FitLabel, string> = {
    great: 'bg-emerald-100 text-emerald-700',
    good: 'bg-blue-100 text-blue-700',
    tight: 'bg-orange-100 text-orange-700',
    loose: 'bg-amber-100 text-amber-700',
    unknown: 'bg-surface-100 text-surface-500',
};

const fitTransKeys: Record<FitLabel, string> = {
    great: 'wardrobe.fitGreat',
    good: 'wardrobe.fitGood',
    tight: 'wardrobe.fitTight',
    loose: 'wardrobe.fitLoose',
    unknown: 'wardrobe.fitUnknown',
};

export function FitBadge({ user, garment, className }: FitBadgeProps) {
    const { t } = useTranslation();
    const result = computeFitScore(user, garment);

    if (result.label === 'unknown') return null;

    return (
        <span
            className={cn(
                'inline-flex items-center gap-1 px-2 py-0.5 rounded-pill text-caption font-medium',
                fitStyles[result.label],
                className,
            )}
            title={result.details.join(', ')}
        >
            {t(fitTransKeys[result.label])}
        </span>
    );
}
