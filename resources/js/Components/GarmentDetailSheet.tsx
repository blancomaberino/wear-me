import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Badge } from '@/Components/ui/Badge';
import { Garment } from '@/types';
import { useForm, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Trash2, Save } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    garment: Garment | null;
    open: boolean;
    onClose: () => void;
}

export default function GarmentDetailSheet({ garment, open, onClose }: Props) {
    const { t } = useTranslation();
    const [confirmDelete, setConfirmDelete] = useState(false);

    const { data, setData, patch, processing } = useForm({
        name: garment?.name || '',
        brand: garment?.brand || '',
        material: garment?.material || '',
        size_label: garment?.size_label || '',
        measurement_chest_cm: garment?.measurement_chest_cm ?? '',
        measurement_length_cm: garment?.measurement_length_cm ?? '',
        measurement_waist_cm: garment?.measurement_waist_cm ?? '',
        measurement_inseam_cm: garment?.measurement_inseam_cm ?? '',
        measurement_shoulder_cm: garment?.measurement_shoulder_cm ?? '',
        measurement_sleeve_cm: garment?.measurement_sleeve_cm ?? '',
    });

    if (!garment) return null;

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('wardrobe.update', garment.id), { onSuccess: onClose });
    };

    const handleDelete = () => {
        router.delete(route('wardrobe.destroy', garment.id), { onSuccess: onClose });
    };

    return (
        <Dialog open={open} onClose={onClose} title={garment.name || garment.original_filename} size="lg">
            <div className="space-y-5">
                {/* Image */}
                <img
                    src={garment.url}
                    alt={garment.name || ''}
                    className="w-full max-h-64 object-contain rounded-card bg-surface-50"
                />

                {/* Info */}
                <div className="flex items-center gap-2">
                    <Badge variant="brand">{garment.category}</Badge>
                    {garment.size_label && <Badge variant="neutral">{garment.size_label}</Badge>}
                    {garment.brand && <Badge variant="neutral">{garment.brand}</Badge>}
                </div>

                {/* Edit Form */}
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-3">
                        <Input label={t('wardrobe.labelName')} value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        <Input label={t('wardrobe.labelBrand')} value={data.brand} onChange={(e) => setData('brand', e.target.value)} />
                        <Input label={t('wardrobe.labelMaterial')} value={data.material} onChange={(e) => setData('material', e.target.value)} />
                        <Input label={t('wardrobe.labelSize')} value={data.size_label} onChange={(e) => setData('size_label', e.target.value)} />
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <Input label={t('wardrobe.chestCm')} type="number" step="0.1" suffix="cm" value={data.measurement_chest_cm} onChange={(e) => setData('measurement_chest_cm', e.target.value as any)} />
                        <Input label={t('wardrobe.lengthCm')} type="number" step="0.1" suffix="cm" value={data.measurement_length_cm} onChange={(e) => setData('measurement_length_cm', e.target.value as any)} />
                        <Input label={t('wardrobe.waistCm')} type="number" step="0.1" suffix="cm" value={data.measurement_waist_cm} onChange={(e) => setData('measurement_waist_cm', e.target.value as any)} />
                        <Input label={t('wardrobe.inseamCm')} type="number" step="0.1" suffix="cm" value={data.measurement_inseam_cm} onChange={(e) => setData('measurement_inseam_cm', e.target.value as any)} />
                    </div>

                    <div className="flex items-center justify-between pt-2">
                        {!confirmDelete ? (
                            <Button type="button" variant="ghost" onClick={() => setConfirmDelete(true)}>
                                <Trash2 className="h-4 w-4" /> {t('common.delete')}
                            </Button>
                        ) : (
                            <div className="flex items-center gap-2 animate-fade-in">
                                <Button type="button" variant="danger" onClick={handleDelete}>{t('wardrobe.confirmDelete')}</Button>
                                <Button type="button" variant="ghost" onClick={() => setConfirmDelete(false)}>{t('common.cancel')}</Button>
                            </div>
                        )}
                        <Button type="submit" loading={processing}>
                            <Save className="h-4 w-4" /> {t('wardrobe.saveChanges')}
                        </Button>
                    </div>
                </form>
            </div>
        </Dialog>
    );
}
