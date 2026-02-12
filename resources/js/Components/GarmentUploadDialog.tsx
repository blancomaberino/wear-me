import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useCallback, useRef, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Upload, Image as ImageIcon, Camera } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface Props {
    open: boolean;
    onClose: () => void;
}

const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

export default function GarmentUploadDialog({ open, onClose }: Props) {
    const { t } = useTranslation();

    const categories = [
        { value: 'upper', label: t('wardrobe.catTop') },
        { value: 'lower', label: t('wardrobe.catBottom') },
        { value: 'dress', label: t('wardrobe.catDress') },
    ];

    const measurementFields: Record<string, { label: string; fields: { key: string; label: string }[] }> = {
        upper: {
            label: t('wardrobe.topMeasurements'),
            fields: [
                { key: 'measurement_chest_cm', label: t('wardrobe.chest') },
                { key: 'measurement_length_cm', label: t('wardrobe.length') },
                { key: 'measurement_shoulder_cm', label: t('wardrobe.shoulder') },
                { key: 'measurement_sleeve_cm', label: t('wardrobe.sleeve') },
            ],
        },
        lower: {
            label: t('wardrobe.bottomMeasurements'),
            fields: [
                { key: 'measurement_waist_cm', label: t('wardrobe.waist') },
                { key: 'measurement_inseam_cm', label: t('wardrobe.inseam') },
                { key: 'measurement_length_cm', label: t('wardrobe.length') },
            ],
        },
        dress: {
            label: t('wardrobe.dressMeasurements'),
            fields: [
                { key: 'measurement_chest_cm', label: t('wardrobe.bust') },
                { key: 'measurement_waist_cm', label: t('wardrobe.waist') },
                { key: 'measurement_length_cm', label: t('wardrobe.length') },
            ],
        },
    };
    const [preview, setPreview] = useState<string | null>(null);
    const [showMeasurements, setShowMeasurements] = useState(false);
    const cameraInputRef = useRef<HTMLInputElement>(null);

    const handleCameraCapture = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('image', file);
            setPreview(URL.createObjectURL(file));
        }
        if (cameraInputRef.current) cameraInputRef.current.value = '';
    };

    const clothingTypes: Record<string, { value: string; label: string }[]> = {
        upper: [
            { value: 't-shirt', label: t('wardrobe.typeShirt') },
            { value: 'blouse', label: t('wardrobe.typeBlouse') },
            { value: 'sweater', label: t('wardrobe.typeSweater') },
            { value: 'jacket', label: t('wardrobe.typeJacket') },
            { value: 'hoodie', label: t('wardrobe.typeHoodie') },
            { value: 'tank-top', label: t('wardrobe.typeTankTop') },
            { value: 'other', label: t('wardrobe.typeOther') },
        ],
        lower: [
            { value: 'jeans', label: t('wardrobe.typeJeans') },
            { value: 'pants', label: t('wardrobe.typePants') },
            { value: 'shorts', label: t('wardrobe.typeShorts') },
            { value: 'skirt', label: t('wardrobe.typeSkirt') },
            { value: 'leggings', label: t('wardrobe.typeLeggings') },
            { value: 'other', label: t('wardrobe.typeOther') },
        ],
        dress: [
            { value: 'casual-dress', label: t('wardrobe.typeCasualDress') },
            { value: 'formal-dress', label: t('wardrobe.typeFormalDress') },
            { value: 'sundress', label: t('wardrobe.typeSundress') },
            { value: 'jumpsuit', label: t('wardrobe.typeJumpsuit') },
            { value: 'other', label: t('wardrobe.typeOther') },
        ],
    };

    const [customType, setCustomType] = useState('');

    const { data, setData, post, processing, errors, reset } = useForm<{
        image: File | null;
        category: string;
        clothing_type: string;
        name: string;
        brand: string;
        material: string;
        size_label: string;
        measurement_chest_cm: string;
        measurement_length_cm: string;
        measurement_waist_cm: string;
        measurement_inseam_cm: string;
        measurement_shoulder_cm: string;
        measurement_sleeve_cm: string;
    }>({
        image: null,
        category: 'upper',
        clothing_type: '',
        name: '',
        brand: '',
        material: '',
        size_label: '',
        measurement_chest_cm: '',
        measurement_length_cm: '',
        measurement_waist_cm: '',
        measurement_inseam_cm: '',
        measurement_shoulder_cm: '',
        measurement_sleeve_cm: '',
    });

    const onDrop = useCallback((files: File[]) => {
        if (files[0]) {
            setData('image', files[0]);
            setPreview(URL.createObjectURL(files[0]));
        }
    }, [setData]);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: { 'image/*': ['.jpg', '.jpeg', '.png', '.webp', '.avif'] },
        maxFiles: 1,
        maxSize: 10 * 1024 * 1024,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('wardrobe.store'), {
            forceFormData: true,
            onSuccess: () => {
                reset();
                setPreview(null);
                setShowMeasurements(false);
                onClose();
            },
        });
    };

    const handleClose = () => {
        reset();
        setPreview(null);
        setShowMeasurements(false);
        setCustomType('');
        onClose();
    };

    const currentMeasurements = measurementFields[data.category];

    return (
        <Dialog open={open} onClose={handleClose} title={t('wardrobe.dialogTitle')} size="lg">
            <form onSubmit={submit} className="space-y-5">
                {/* Image Dropzone */}
                <input
                    ref={cameraInputRef}
                    type="file"
                    accept="image/*"
                    capture="environment"
                    onChange={handleCameraCapture}
                    className="hidden"
                />
                {!preview ? (
                    <div className="space-y-3">
                        <div
                            {...getRootProps()}
                            className={cn(
                                'border-2 border-dashed rounded-card p-8 text-center cursor-pointer transition-colors',
                                isDragActive ? 'border-brand-500 bg-brand-50' : 'border-surface-300 hover:border-brand-400 hover:bg-surface-50',
                            )}
                        >
                            <input {...getInputProps()} />
                            <Upload className="h-8 w-8 text-surface-400 mx-auto mb-3" />
                            <p className="text-body-sm text-surface-600 font-medium">{t('wardrobe.dropImage')}</p>
                            <p className="text-caption text-surface-400 mt-1">{t('wardrobe.imageHint')}</p>
                        </div>
                        <button
                            type="button"
                            onClick={() => cameraInputRef.current?.click()}
                            className="w-full flex items-center justify-center gap-2 py-2.5 rounded-button border border-surface-200 bg-white text-surface-600 hover:bg-surface-50 hover:border-surface-300 transition-colors text-body-sm font-medium"
                        >
                            <Camera className="h-4 w-4" /> {t('wardrobe.takePhoto')}
                        </button>
                    </div>
                ) : (
                    <div className="relative">
                        <img src={preview} alt="Preview" className="w-full h-48 object-contain rounded-card bg-surface-50" />
                        <button
                            type="button"
                            onClick={() => { setPreview(null); setData('image', null); }}
                            className="absolute top-2 right-2 bg-white rounded-full p-1.5 shadow-soft text-surface-500 hover:text-surface-700"
                        >
                            ×
                        </button>
                    </div>
                )}
                {errors.image && <p className="text-caption text-red-600">{errors.image}</p>}

                {/* Category Pills */}
                <div>
                    <label className="block text-body-sm font-medium text-surface-700 mb-2">{t('wardrobe.category')}</label>
                    <div className="flex gap-2">
                        {categories.map((cat) => (
                            <button
                                key={cat.value}
                                type="button"
                                onClick={() => { setData('category', cat.value); setData('clothing_type', ''); setCustomType(''); }}
                                className={cn(
                                    'px-4 py-1.5 rounded-pill text-body-sm font-medium transition-colors',
                                    data.category === cat.value
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-surface-100 text-surface-600 hover:bg-surface-200',
                                )}
                            >
                                {cat.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Clothing Type */}
                <div>
                    <label className="block text-body-sm font-medium text-surface-700 mb-2">{t('wardrobe.clothingType')}</label>
                    <select
                        value={data.clothing_type}
                        onChange={(e) => {
                            const val = e.target.value;
                            setData('clothing_type', val);
                            if (val !== 'other') setCustomType('');
                        }}
                        className="w-full rounded-input border border-surface-300 px-3 py-2 text-body-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 bg-white"
                    >
                        <option value="">{t('wardrobe.selectType')}</option>
                        {(clothingTypes[data.category] || []).map((ct) => (
                            <option key={ct.value} value={ct.value}>{ct.label}</option>
                        ))}
                    </select>
                    {data.clothing_type === 'other' && (
                        <Input
                            className="mt-2"
                            value={customType}
                            onChange={(e) => { setCustomType(e.target.value); setData('clothing_type', e.target.value || 'other'); }}
                            placeholder={t('wardrobe.customTypePlaceholder')}
                        />
                    )}
                </div>

                {/* Basic Info */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Input label={t('wardrobe.labelName')} value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder={t('wardrobe.placeholderName')} error={errors.name} />
                    <Input label={t('wardrobe.labelBrand')} value={data.brand} onChange={(e) => setData('brand', e.target.value)} placeholder={t('wardrobe.placeholderBrand')} />
                </div>
                <Input label={t('wardrobe.labelMaterial')} value={data.material} onChange={(e) => setData('material', e.target.value)} placeholder={t('wardrobe.placeholderMaterial')} />

                {/* Size Pills */}
                <div>
                    <label className="block text-body-sm font-medium text-surface-700 mb-2">{t('wardrobe.labelSize')}</label>
                    <div className="flex flex-wrap gap-2">
                        {sizes.map((size) => (
                            <button
                                key={size}
                                type="button"
                                onClick={() => setData('size_label', data.size_label === size ? '' : size)}
                                className={cn(
                                    'px-3 py-1 rounded-pill text-body-sm font-medium transition-colors',
                                    data.size_label === size
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-surface-100 text-surface-600 hover:bg-surface-200',
                                )}
                            >
                                {size}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Measurements Toggle */}
                {!showMeasurements ? (
                    <button
                        type="button"
                        onClick={() => setShowMeasurements(true)}
                        className="text-body-sm text-brand-600 hover:text-brand-700 font-medium"
                    >
                        {t('wardrobe.addMeasurements')}
                    </button>
                ) : currentMeasurements && (
                    <div className="space-y-3 animate-fade-in">
                        <label className="block text-body-sm font-medium text-surface-700">{currentMeasurements.label} (cm)</label>
                        <div className="grid grid-cols-2 gap-3">
                            {currentMeasurements.fields.map((field) => (
                                <Input
                                    key={field.key}
                                    label={field.label}
                                    type="number"
                                    step="0.1"
                                    suffix="cm"
                                    value={(data as any)[field.key]}
                                    onChange={(e) => setData(field.key as any, e.target.value)}
                                />
                            ))}
                        </div>
                        <button
                            type="button"
                            onClick={() => setShowMeasurements(false)}
                            className="text-caption text-surface-400 hover:text-surface-600"
                        >
                            {t('wardrobe.skipMeasurements')}
                        </button>
                    </div>
                )}

                {/* Actions */}
                <div className="flex items-center justify-end gap-3 pt-2">
                    <Button type="button" variant="ghost" onClick={handleClose}>{t('common.cancel')}</Button>
                    <Button type="submit" loading={processing} disabled={!data.image}>
                        <Upload className="h-4 w-4" /> {t('common.upload')}
                    </Button>
                </div>
            </form>
        </Dialog>
    );
}
