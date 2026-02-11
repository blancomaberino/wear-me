import { useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Toggle } from '@/Components/ui/Toggle';
import { Card, CardBody, CardHeader } from '@/Components/ui/Card';
import { User } from '@/types';
import { FormEventHandler, useState } from 'react';
import { Ruler, Info } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    user: User;
}

const cmToIn = (cm: number | null | undefined): string => {
    if (cm === null || cm === undefined) return '';
    return String(Math.round(cm / 2.54 * 10) / 10);
};

const inToCm = (inches: string): string => {
    const val = parseFloat(inches);
    if (isNaN(val)) return '';
    return String(Math.round(val * 2.54 * 10) / 10);
};

const kgToLb = (kg: number | null | undefined): string => {
    if (kg === null || kg === undefined) return '';
    return String(Math.round(kg * 2.205 * 10) / 10);
};

const lbToKg = (lb: string): string => {
    const val = parseFloat(lb);
    if (isNaN(val)) return '';
    return String(Math.round(val / 2.205 * 10) / 10);
};

export default function BodyMeasurementsForm({ user }: Props) {
    const { t } = useTranslation();
    const [isImperial, setIsImperial] = useState(user.measurement_unit === 'imperial');
    const [showWhy, setShowWhy] = useState(false);

    const { data, setData, patch, processing, recentlySuccessful } = useForm({
        measurement_unit: user.measurement_unit || 'metric',
        height_cm: user.height_cm ?? '',
        weight_kg: user.weight_kg ?? '',
        chest_cm: user.chest_cm ?? '',
        waist_cm: user.waist_cm ?? '',
        hips_cm: user.hips_cm ?? '',
        inseam_cm: user.inseam_cm ?? '',
        shoe_size_eu: user.shoe_size_eu ?? '',
    });

    const handleUnitToggle = (imperial: boolean) => {
        setIsImperial(imperial);
        setData('measurement_unit', imperial ? 'imperial' : 'metric');
    };

    const getDisplayValue = (field: string): string => {
        const val = data[field as keyof typeof data];
        if (val === '' || val === null || val === undefined) return '';
        if (!isImperial) return String(val);
        if (field === 'weight_kg') return kgToLb(Number(val));
        if (field === 'shoe_size_eu') return String(val);
        return cmToIn(Number(val));
    };

    const handleChange = (field: string, displayValue: string) => {
        if (displayValue === '') {
            setData(field as any, '');
            return;
        }
        if (!isImperial) {
            setData(field as any, displayValue);
            return;
        }
        if (field === 'weight_kg') {
            setData(field as any, lbToKg(displayValue));
        } else if (field === 'shoe_size_eu') {
            setData(field as any, displayValue);
        } else {
            setData(field as any, inToCm(displayValue));
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('profile.measurements.update'));
    };

    const unit = isImperial ? 'in' : 'cm';
    const weightUnit = isImperial ? 'lb' : 'kg';

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="flex items-center justify-center h-10 w-10 rounded-xl bg-brand-50">
                            <Ruler className="h-5 w-5 text-brand-600" />
                        </div>
                        <div>
                            <h2 className="text-heading-sm text-surface-900">{t('profile.measurementsTitle')}</h2>
                            <p className="text-body-sm text-surface-500">{t('profile.measurementsDesc')}</p>
                        </div>
                    </div>
                    <Toggle
                        labelLeft={t('profile.unitMetric')}
                        labelRight={t('profile.unitImperial')}
                        enabled={isImperial}
                        onChange={handleUnitToggle}
                    />
                </div>
            </CardHeader>
            <CardBody>
                <form onSubmit={submit} className="space-y-6">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Input
                            label={t('profile.height')}
                            type="number"
                            step="0.1"
                            suffix={unit}
                            placeholder={isImperial ? 'e.g. 68.9' : 'e.g. 175.0'}
                            value={getDisplayValue('height_cm')}
                            onChange={(e) => handleChange('height_cm', e.target.value)}
                        />
                        <Input
                            label={t('profile.weight')}
                            type="number"
                            step="0.1"
                            suffix={weightUnit}
                            placeholder={isImperial ? 'e.g. 154.3' : 'e.g. 70.0'}
                            value={getDisplayValue('weight_kg')}
                            onChange={(e) => handleChange('weight_kg', e.target.value)}
                        />
                        <Input
                            label={t('profile.chest')}
                            type="number"
                            step="0.1"
                            suffix={unit}
                            placeholder={isImperial ? 'e.g. 37.8' : 'e.g. 96.0'}
                            value={getDisplayValue('chest_cm')}
                            onChange={(e) => handleChange('chest_cm', e.target.value)}
                        />
                        <Input
                            label={t('profile.waist')}
                            type="number"
                            step="0.1"
                            suffix={unit}
                            placeholder={isImperial ? 'e.g. 32.3' : 'e.g. 82.0'}
                            value={getDisplayValue('waist_cm')}
                            onChange={(e) => handleChange('waist_cm', e.target.value)}
                        />
                        <Input
                            label={t('profile.hips')}
                            type="number"
                            step="0.1"
                            suffix={unit}
                            placeholder={isImperial ? 'e.g. 37.8' : 'e.g. 96.0'}
                            value={getDisplayValue('hips_cm')}
                            onChange={(e) => handleChange('hips_cm', e.target.value)}
                        />
                        <Input
                            label={t('profile.inseam')}
                            type="number"
                            step="0.1"
                            suffix={unit}
                            placeholder={isImperial ? 'e.g. 31.5' : 'e.g. 80.0'}
                            value={getDisplayValue('inseam_cm')}
                            onChange={(e) => handleChange('inseam_cm', e.target.value)}
                        />
                        <Input
                            label={t('profile.shoeSize')}
                            type="number"
                            step="0.5"
                            suffix="EU"
                            placeholder="e.g. 42.0"
                            value={getDisplayValue('shoe_size_eu')}
                            onChange={(e) => handleChange('shoe_size_eu', e.target.value)}
                        />
                    </div>

                    <button
                        type="button"
                        onClick={() => setShowWhy(!showWhy)}
                        className="inline-flex items-center gap-1.5 text-body-sm text-surface-500 hover:text-surface-700 transition-colors"
                    >
                        <Info className="h-4 w-4" />
                        {t('profile.whyTitle')}
                    </button>

                    {showWhy && (
                        <div className="rounded-input bg-surface-50 p-4 text-body-sm text-surface-600 animate-fade-in">
                            {t('profile.whyDesc')}
                        </div>
                    )}

                    <div className="flex items-center gap-4">
                        <Button type="submit" loading={processing}>
                            {t('profile.saveMeasurements')}
                        </Button>
                        {recentlySuccessful && (
                            <p className="text-body-sm text-emerald-600 animate-fade-in">{t('profile.saved')}</p>
                        )}
                    </div>
                </form>
            </CardBody>
        </Card>
    );
}
