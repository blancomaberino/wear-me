import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { Dialog } from '@/Components/ui/Dialog';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Head, router } from '@inertiajs/react';
import { ModelImage } from '@/types';
import { useCallback, useRef, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Camera, Upload, Star, Trash2 } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface Props {
    images: ModelImage[];
}

export default function Index({ images }: Props) {
    const { t } = useTranslation();
    const [uploading, setUploading] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<ModelImage | null>(null);
    const cameraInputRef = useRef<HTMLInputElement>(null);

    const handleCameraCapture = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;
        setUploading(true);
        const formData = new FormData();
        formData.append('image', file);
        router.post(route('model-images.store'), formData as any, {
            forceFormData: true,
            onFinish: () => setUploading(false),
        });
        if (cameraInputRef.current) cameraInputRef.current.value = '';
    }, []);

    const onDrop = useCallback((files: File[]) => {
        if (!files[0]) return;
        setUploading(true);
        const formData = new FormData();
        formData.append('image', files[0]);
        router.post(route('model-images.store'), formData as any, {
            forceFormData: true,
            onFinish: () => setUploading(false),
        });
    }, []);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: { 'image/*': ['.jpg', '.jpeg', '.png', '.webp'] },
        maxFiles: 1,
        maxSize: 10 * 1024 * 1024,
        disabled: uploading,
    });

    const handleSetPrimary = (image: ModelImage) => {
        router.patch(route('model-images.primary', image.id));
    };

    const handleDelete = () => {
        if (!deleteTarget) return;
        router.delete(route('model-images.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('photos.title')} />

            <PageHeader
                title={t('photos.title')}
                description={t('photos.count', { count: images.length })}
            />

            {/* Upload Dropzone */}
            <input
                ref={cameraInputRef}
                type="file"
                accept="image/*"
                capture="user"
                onChange={handleCameraCapture}
                className="hidden"
            />
            <div className="mb-8 space-y-3">
                <div
                    {...getRootProps()}
                    className={cn(
                        'border-2 border-dashed rounded-card p-10 text-center cursor-pointer transition-all',
                        isDragActive ? 'border-brand-500 bg-brand-50' : 'border-surface-300 hover:border-brand-400 hover:bg-surface-50',
                        uploading && 'pointer-events-none opacity-50',
                    )}
                >
                    <input {...getInputProps()} />
                    {uploading ? (
                        <div className="flex flex-col items-center">
                            <div className="h-8 w-8 border-2 border-brand-200 border-t-brand-600 rounded-full animate-spin mb-3" />
                            <p className="text-body-sm text-surface-600 font-medium">{t('common.uploading')}</p>
                        </div>
                    ) : (
                        <>
                            <div className="flex items-center justify-center h-14 w-14 rounded-2xl bg-brand-50 mx-auto mb-4">
                                <Upload className="h-7 w-7 text-brand-600" />
                            </div>
                            <p className="text-body font-medium text-surface-700 mb-1">
                                {isDragActive ? t('photos.dropPrompt') : t('photos.uploadPrompt')}
                            </p>
                            <p className="text-body-sm text-surface-400">{t('photos.uploadHint')}</p>
                        </>
                    )}
                </div>
                {!uploading && (
                    <button
                        type="button"
                        onClick={() => cameraInputRef.current?.click()}
                        className="w-full flex items-center justify-center gap-2 py-3 rounded-card border border-surface-200 bg-white text-surface-600 hover:bg-surface-50 hover:border-surface-300 transition-colors text-body-sm font-medium"
                    >
                        <Camera className="h-5 w-5" /> {t('photos.takePhoto')}
                    </button>
                )}
            </div>

            {/* Gallery */}
            {images.length === 0 ? (
                <EmptyState
                    icon={Camera}
                    title={t('photos.emptyTitle')}
                    description={t('photos.emptyDesc')}
                />
            ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    {images.map((image) => (
                        <div key={image.id} className="group relative aspect-square rounded-card overflow-hidden bg-surface-50">
                            <img
                                src={image.thumbnail_url || image.url}
                                alt={image.original_filename}
                                className="w-full h-full object-cover"
                                loading="lazy"
                            />

                            {/* Primary badge */}
                            {image.is_primary && (
                                <Badge variant="brand" className="absolute top-2 left-2">
                                    <Star className="h-3 w-3 fill-current mr-0.5" /> {t('photos.primary')}
                                </Badge>
                            )}

                            {/* Hover actions */}
                            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-end justify-center pb-3 opacity-0 group-hover:opacity-100">
                                <div className="flex items-center gap-2">
                                    {!image.is_primary && (
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            onClick={() => handleSetPrimary(image)}
                                        >
                                            <Star className="h-3.5 w-3.5" /> {t('photos.setPrimary')}
                                        </Button>
                                    )}
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        onClick={() => setDeleteTarget(image)}
                                    >
                                        <Trash2 className="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>

                            {/* Date */}
                            <div className="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/50 to-transparent p-2 pt-6 group-hover:opacity-0 transition-opacity">
                                <p className="text-caption text-white/80">{image.created_at}</p>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Delete Dialog */}
            <Dialog
                open={!!deleteTarget}
                onClose={() => setDeleteTarget(null)}
                title={t('photos.deleteTitle')}
                description={t('photos.deleteDesc')}
                size="sm"
            >
                <div className="flex items-center justify-end gap-3 mt-4">
                    <Button variant="ghost" onClick={() => setDeleteTarget(null)}>{t('common.cancel')}</Button>
                    <Button variant="danger" onClick={handleDelete}>{t('common.delete')}</Button>
                </div>
            </Dialog>
        </AuthenticatedLayout>
    );
}
