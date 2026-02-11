import { ImageSelector } from '@/Components/ui/ImageSelector';
import { Badge } from '@/Components/ui/Badge';
import { Button } from '@/Components/ui/Button';
import { ModelImage } from '@/types';
import { useCallback, useRef, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Camera, Upload, X } from 'lucide-react';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

interface Props {
    modelImages: ModelImage[];
    selectedPhotoId: number | null;
    onSelectPhoto: (id: number | null) => void;
    sourceResult: { id: number; result_url: string } | null;
    onClearSource: () => void;
}

export default function SelectPhoto({ modelImages, selectedPhotoId, onSelectPhoto, sourceResult, onClearSource }: Props) {
    const { t } = useTranslation();
    const [uploading, setUploading] = useState(false);
    const cameraInputRef = useRef<HTMLInputElement>(null);

    const handleCameraCapture = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;
        setUploading(true);
        const formData = new FormData();
        formData.append('image', file);
        router.post(route('model-images.store'), formData as any, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => setUploading(false),
            onError: () => setUploading(false),
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
            preserveScroll: true,
            onSuccess: () => setUploading(false),
            onError: () => setUploading(false),
        });
    }, []);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: { 'image/*': ['.jpg', '.jpeg', '.png', '.webp'] },
        maxFiles: 1,
        maxSize: 10 * 1024 * 1024,
        disabled: uploading,
    });

    if (sourceResult) {
        return (
            <div className="space-y-4">
                <h3 className="text-heading-sm text-surface-900">{t('tryon.usingPrevious')}</h3>
                <div className="relative inline-block">
                    <img
                        src={sourceResult.result_url}
                        alt="Previous result"
                        className="w-48 h-48 object-cover rounded-card ring-[3px] ring-brand-500 ring-offset-2"
                    />
                    <Badge variant="brand" className="absolute -top-2 -right-2">{t('tryon.previousResult')}</Badge>
                </div>
                <button onClick={onClearSource} className="flex items-center gap-1.5 text-body-sm text-surface-500 hover:text-surface-700">
                    <X className="h-4 w-4" /> {t('tryon.useDifferentPhoto')}
                </button>
            </div>
        );
    }

    const uploadCard = (
        <div className="space-y-2">
            <input
                ref={cameraInputRef}
                type="file"
                accept="image/*"
                capture="user"
                onChange={handleCameraCapture}
                className="hidden"
            />
            <div
                {...getRootProps()}
                className={cn(
                    'aspect-square rounded-xl border-2 border-dashed flex flex-col items-center justify-center cursor-pointer transition-colors',
                    isDragActive ? 'border-brand-500 bg-brand-50' : 'border-surface-300 hover:border-brand-400 hover:bg-surface-50',
                    uploading && 'pointer-events-none opacity-50',
                )}
            >
                <input {...getInputProps()} />
                {uploading ? (
                    <div className="h-6 w-6 border-2 border-surface-300 border-t-brand-600 rounded-full animate-spin" />
                ) : (
                    <>
                        <Upload className="h-6 w-6 text-surface-400 mb-1.5" />
                        <span className="text-caption text-surface-500 font-medium">{t('tryon.uploadNew')}</span>
                    </>
                )}
            </div>
            {!uploading && (
                <button
                    type="button"
                    onClick={() => cameraInputRef.current?.click()}
                    className="w-full flex items-center justify-center gap-1.5 py-1.5 rounded-lg border border-surface-200 bg-white text-surface-600 hover:bg-surface-50 transition-colors text-caption font-medium"
                >
                    <Camera className="h-3.5 w-3.5" /> {t('tryon.takePhoto')}
                </button>
            )}
        </div>
    );

    const items = modelImages.map((img) => ({
        id: img.id,
        url: img.url,
        thumbnail_url: img.thumbnail_url,
    }));

    return (
        <div className="space-y-4">
            <div>
                <h3 className="text-heading-sm text-surface-900 mb-1">{t('tryon.selectPhotoTitle')}</h3>
                <p className="text-body-sm text-surface-500">{t('tryon.selectPhotoDesc')}</p>
            </div>
            <ImageSelector
                items={items}
                selectedId={selectedPhotoId}
                onSelect={onSelectPhoto}
                columns={5}
                prependSlot={uploadCard}
                emptyText={t('tryon.noPhotos')}
            />
        </div>
    );
}
