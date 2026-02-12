import { useCallback, useRef } from 'react';
import { useDropzone } from 'react-dropzone';
import { Upload, Image as ImageIcon, Camera } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface ImageUploaderProps {
    onFileSelected: (file: File) => void;
    uploading?: boolean;
    progress?: number;
    accept?: string[];
    label?: string;
}

export default function ImageUploader({
    onFileSelected,
    uploading = false,
    progress = 0,
    accept = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'],
    label = 'Upload Image',
}: ImageUploaderProps) {
    const { t } = useTranslation();
    const cameraInputRef = useRef<HTMLInputElement>(null);
    const isMobile = typeof navigator !== 'undefined' && /Mobi|Android/i.test(navigator.userAgent);

    const handleCameraCapture = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) onFileSelected(file);
        if (cameraInputRef.current) cameraInputRef.current.value = '';
    };
    const onDrop = useCallback((acceptedFiles: File[]) => {
        if (acceptedFiles.length > 0) {
            onFileSelected(acceptedFiles[0]);
        }
    }, [onFileSelected]);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: accept.reduce((acc, type) => ({ ...acc, [type]: [] }), {}),
        maxFiles: 1,
        maxSize: 10 * 1024 * 1024,
        disabled: uploading,
    });

    return (
        <div className="space-y-3">
            <input
                ref={cameraInputRef}
                type="file"
                accept="image/*"
                {...(isMobile ? { capture: 'environment' } : {})}
                onChange={handleCameraCapture}
                className="hidden"
            />
            <div
                {...getRootProps()}
                className={`relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-200 ${
                    isDragActive
                        ? 'border-brand-500 bg-brand-50'
                        : 'border-surface-300 hover:border-brand-400 hover:bg-surface-50'
                } ${uploading ? 'opacity-60 cursor-not-allowed' : ''}`}
            >
                <input {...getInputProps()} />
                <div className="flex flex-col items-center gap-3">
                    {uploading ? (
                        <>
                            <div className="w-12 h-12 rounded-full border-4 border-brand-200 border-t-brand-600 animate-spin" />
                            <p className="text-sm text-surface-600">{t('common.uploadingProgress', { progress })}</p>
                            <div className="w-full max-w-xs bg-surface-200 rounded-full h-2">
                                <div
                                    className="bg-brand-600 h-2 rounded-full transition-all duration-300"
                                    style={{ width: `${progress}%` }}
                                />
                            </div>
                        </>
                    ) : isDragActive ? (
                        <>
                            <Upload className="w-12 h-12 text-brand-500" />
                            <p className="text-lg font-medium text-brand-600">{t('common.dropHere')}</p>
                        </>
                    ) : (
                        <>
                            <ImageIcon className="w-12 h-12 text-surface-400" />
                            <p className="text-lg font-medium text-surface-700">{label}</p>
                            <p className="text-sm text-surface-500">
                                {t('common.dragDrop')}
                            </p>
                            <p className="text-xs text-surface-400">{t('common.fileHint')}</p>
                        </>
                    )}
                </div>
            </div>
            {!uploading && (
                <button
                    type="button"
                    onClick={() => cameraInputRef.current?.click()}
                    className="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-surface-200 bg-white text-surface-600 hover:bg-surface-50 hover:border-surface-300 transition-colors text-sm font-medium"
                >
                    <Camera className="w-4 h-4" /> {t('common.takePhoto')}
                </button>
            )}
        </div>
    );
}
