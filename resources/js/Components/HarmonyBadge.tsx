import { cn } from '@/lib/utils';
import { getHarmonyScore, getHarmonyType, HarmonyType } from '@/lib/colorHarmony';
import { useTranslation } from 'react-i18next';

interface HarmonyBadgeProps {
    colors: string[];
    score?: number | null;
    className?: string;
    showLabel?: boolean;
}

const harmonyColors: Record<HarmonyType, string> = {
    monochromatic: 'bg-emerald-100 text-emerald-700',
    analogous: 'bg-blue-100 text-blue-700',
    complementary: 'bg-purple-100 text-purple-700',
    triadic: 'bg-amber-100 text-amber-700',
    'split-complementary': 'bg-orange-100 text-orange-700',
    neutral: 'bg-surface-100 text-surface-600',
    clashing: 'bg-red-100 text-red-700',
};

export function HarmonyBadge({ colors, score: propScore, className, showLabel = true }: HarmonyBadgeProps) {
    const { t } = useTranslation();

    const computedScore = propScore ?? (colors.length >= 2 ? getHarmonyScore(colors) : null);
    if (computedScore == null) return null;

    const type = colors.length >= 2 ? getHarmonyType(colors) : 'neutral';

    return (
        <span
            className={cn(
                'inline-flex items-center gap-1 px-2 py-0.5 rounded-pill text-caption font-medium',
                harmonyColors[type],
                className,
            )}
            title={`${t('wardrobe.harmonyScore')}: ${computedScore}/100 (${type})`}
        >
            <span className="font-semibold">{computedScore}</span>
            {showLabel && <span>{t('wardrobe.harmonyScore')}</span>}
        </span>
    );
}
