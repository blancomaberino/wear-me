import { Input } from '@/Components/ui/Input';
import { Card, CardBody } from '@/Components/ui/Card';
import { Badge } from '@/Components/ui/Badge';
import { User, Garment } from '@/types';
import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { Ruler, Info } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useRef } from 'react';

interface Props {
    promptHint: string;
    onPromptChange: (value: string) => void;
    user: User;
    selectedGarments: Garment[];
}

export default function StylePreferences({ promptHint, onPromptChange, user, selectedGarments }: Props) {
    const { t } = useTranslation();
    const customInputRef = useRef<HTMLInputElement>(null);

    const styleChips = [
        { value: 'casual', label: t('tryon.chipCasual') },
        { value: 'formal', label: t('tryon.chipFormal') },
        { value: 'streetwear', label: t('tryon.chipStreetwear') },
        { value: 'business', label: t('tryon.chipBusiness') },
        { value: 'relaxed', label: t('tryon.chipRelaxed') },
        { value: 'sporty', label: t('tryon.chipSporty') },
    ];
    const toggleChip = (chipValue: string) => {
        if (promptHint.includes(chipValue)) {
            onPromptChange(promptHint.replace(chipValue, '').replace(/\s{2,}/g, ' ').trim());
        } else {
            onPromptChange((promptHint + ' ' + chipValue).trim());
        }
    };

    const hasMeasurements = user.has_measurements || user.height_cm || user.chest_cm || user.waist_cm;

    return (
        <div className="space-y-6">
            <div>
                <h3 className="text-heading-sm text-surface-900 mb-1">{t('tryon.styleTitle')}</h3>
                <p className="text-body-sm text-surface-500">{t('tryon.styleDesc')}</p>
            </div>

            {/* Style chips */}
            <div>
                <label className="block text-body-sm font-medium text-surface-700 mb-2">{t('tryon.quickStyles')}</label>
                <div className="flex flex-wrap gap-2">
                    {styleChips.map((chip) => {
                        const isActive = promptHint.includes(chip.value);
                        return (
                            <button
                                key={chip.value}
                                type="button"
                                onClick={() => toggleChip(chip.value)}
                                className={cn(
                                    'px-3 py-1.5 rounded-pill text-body-sm font-medium transition-colors',
                                    isActive
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-surface-100 text-surface-600 hover:bg-surface-200',
                                )}
                            >
                                {chip.label}
                            </button>
                        );
                    })}
                    <button
                        type="button"
                        onClick={() => customInputRef.current?.focus()}
                        className="px-3 py-1.5 rounded-pill text-body-sm font-medium transition-colors border border-dashed border-surface-300 text-surface-500 hover:border-brand-400 hover:text-brand-600"
                    >
                        {t('tryon.chipOther')}
                    </button>
                </div>
            </div>

            {/* Custom instructions */}
            <Input
                ref={customInputRef}
                label={t('tryon.additionalInstructions')}
                value={promptHint}
                onChange={(e) => onPromptChange(e.target.value)}
                placeholder={t('tryon.instructionsPlaceholder')}
                description={t('tryon.instructionsHint')}
            />

            {/* Measurement summary */}
            <Card>
                <CardBody>
                    <div className="flex items-center gap-3 mb-3">
                        <Ruler className="h-5 w-5 text-surface-400" />
                        <span className="text-body-sm font-medium text-surface-700">{t('tryon.measurementContext')}</span>
                    </div>
                    {hasMeasurements ? (
                        <div className="flex flex-wrap gap-2">
                            {user.height_cm && <Badge variant="neutral">{t('tryon.height')}: {user.height_cm}cm</Badge>}
                            {user.weight_kg && <Badge variant="neutral">{t('tryon.weight')}: {user.weight_kg}kg</Badge>}
                            {user.chest_cm && <Badge variant="neutral">{t('tryon.chest')}: {user.chest_cm}cm</Badge>}
                            {user.waist_cm && <Badge variant="neutral">{t('tryon.waist')}: {user.waist_cm}cm</Badge>}
                            {user.hips_cm && <Badge variant="neutral">{t('tryon.hips')}: {user.hips_cm}cm</Badge>}
                            {selectedGarments.map((g) =>
                                g.size_label ? <Badge key={g.id} variant="brand">{g.name}: {g.size_label}</Badge> : null,
                            )}
                        </div>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Info className="h-4 w-4 text-surface-400" />
                            <span className="text-body-sm text-surface-500">
                                {t('tryon.noMeasurements')}{' '}
                                <Link href={route('profile.edit')} className="text-brand-600 hover:text-brand-700 font-medium">
                                    {t('tryon.addMeasurements')}
                                </Link>{' '}
                                {t('tryon.betterResults')}
                            </span>
                        </div>
                    )}
                </CardBody>
            </Card>
        </div>
    );
}
