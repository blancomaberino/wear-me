import { Dialog } from '@/Components/ui/Dialog';
import { Button } from '@/Components/ui/Button';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useCallback, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Upload, X, Image as ImageIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface Props {
    open: boolean;
    onClose: () => void;
}

export default function BulkUploadDialog({ open, onClose }: Props) {
    const { t } = useTranslation();
    const [previews, setPreviews] = useState<{ file: File; preview: string }[]>([]);

    const categories = [
        { value: 'upper', label: t('wardrobe.catTop') },
        { value: 'lower', label: t('wardrobe.catBottom') },
        { value: 'dress', label: t('wardrobe.catDress') },
    ];

    const { data, setData, post, processing, errors, reset } = useForm<{
        images: File[];
        category: string;
    }>({
        images: [],
        category: 'upper',
    });

    const onDrop = useCallback((acceptedFiles: File[]) => {
        const maxFiles = 20;
        const currentCount = previews.length;
        const remaining = maxFiles - currentCount;
        const newFiles = acceptedFiles.slice(0, remaining);

        const newPreviews = newFiles.map((file) => ({
            file,
            preview: URL.createObjectURL(file),
        }));

        setPreviews((prev) => [...prev, ...newPreviews]);
        setData('images', [...data.images, ...newFiles]);
    }, [previews, data.images, setData]);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: { 'image/*': ['.jpg', '.jpeg', '.png', '.webp', '.avif'] },
        maxSize: 10 * 1024 * 1024,
        maxFiles: 20,
    });

    const removeFile = (index: number) => {
        URL.revokeObjectURL(previews[index].preview);
        const newPreviews = previews.filter((_, i) => i !== index);
        const newFiles = data.images.filter((_, i) => i !== index);
        setPreviews(newPreviews);
        setData('images', newFiles);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('wardrobe.bulk'), {
            forceFormData: true,
            onSuccess: () => {
                handleClose();
            },
        });
    };

    const handleClose = () => {
        previews.forEach((p) => URL.revokeObjectURL(p.preview));
        setPreviews([]);
        reset();
        onClose();
    };

    return (
        <Dialog open={open} onClose={handleClose} title={t('import.bulkTitle')} size="lg">
            <form onSubmit={submit} className="space-y-5">
                {/* Dropzone */}
                <div
                    {...getRootProps()}
                    className={cn(
                        'border-2 border-dashed rounded-card p-6 text-center cursor-pointer transition-colors',
                        isDragActive ? 'border-brand-500 bg-brand-50' : 'border-surface-300 hover:border-brand-400 hover:bg-surface-50',
                    )}
                >
                    <input {...getInputProps()} />
                    <Upload className="h-8 w-8 text-surface-400 mx-auto mb-2" />
                    <p className="text-body-sm text-surface-600 font-medium">{t('import.bulkDrop')}</p>
                    <p className="text-caption text-surface-400 mt-1">{t('import.bulkHint')}</p>
                </div>

                {/* Preview Grid */}
                {previews.length > 0 && (
                    <div className="space-y-3">
                        <p className="text-body-sm text-surface-600">
                            {previews.length} / 20 {t('common.selected')}
                        </p>
                        <div className="grid grid-cols-4 sm:grid-cols-5 gap-2 max-h-48 overflow-y-auto">
                            {previews.map((p, i) => (
                                <div key={i} className="relative group">
                                    <img
                                        src={p.preview}
                                        alt={`Preview ${i + 1}`}
                                        className="w-full h-16 object-cover rounded-input bg-surface-50"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => removeFile(i)}
                                        className="absolute -top-1 -right-1 bg-white rounded-full p-0.5 shadow-soft text-surface-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <X className="h-3 w-3" />
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {errors.images && <p className="text-caption text-red-600">{errors.images}</p>}

                {/* Category Selection */}
                <div>
                    <label className="block text-body-sm font-medium text-surface-700 mb-2">{t('import.bulkCategory')}</label>
                    <div className="flex gap-2">
                        {categories.map((cat) => (
                            <button
                                key={cat.value}
                                type="button"
                                onClick={() => setData('category', cat.value)}
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

                {/* Actions */}
                <div className="flex items-center justify-end gap-3 pt-2">
                    <Button type="button" variant="ghost" onClick={handleClose}>{t('common.cancel')}</Button>
                    <Button type="submit" loading={processing} disabled={data.images.length === 0}>
                        <Upload className="h-4 w-4" /> {t('common.upload')} ({data.images.length})
                    </Button>
                </div>
            </form>
        </Dialog>
    );
}
