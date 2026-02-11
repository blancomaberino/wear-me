import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';

interface UseImageUploadOptions {
    routeName: string;
    extraData?: Record<string, string>;
    onSuccess?: () => void;
}

export function useImageUpload({ routeName, extraData = {}, onSuccess }: UseImageUploadOptions) {
    const [uploading, setUploading] = useState(false);
    const [progress, setProgress] = useState(0);

    const upload = useCallback((file: File) => {
        const formData = new FormData();
        formData.append('image', file);
        Object.entries(extraData).forEach(([key, value]) => {
            formData.append(key, value);
        });

        setUploading(true);
        setProgress(0);

        router.post(route(routeName), formData as any, {
            forceFormData: true,
            onProgress: (event) => {
                if (event?.percentage) setProgress(event.percentage);
            },
            onSuccess: () => {
                setUploading(false);
                setProgress(0);
                onSuccess?.();
            },
            onError: () => {
                setUploading(false);
                setProgress(0);
            },
        });
    }, [routeName, extraData, onSuccess]);

    return { upload, uploading, progress };
}
