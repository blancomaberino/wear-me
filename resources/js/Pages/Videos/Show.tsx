import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ProcessingStatus from '@/Components/ProcessingStatus';
import VideoPlayer from '@/Components/VideoPlayer';
import { Head, Link } from '@inertiajs/react';
import { TryOnVideo } from '@/types';
import { usePolling } from '@/hooks/usePolling';
import { useState } from 'react';
import { PageHeader } from '@/Components/layout/PageHeader';
import { useTranslation } from 'react-i18next';

interface Props {
    video: TryOnVideo;
}

export default function Show({ video: initial }: Props) {
    const { t } = useTranslation();
    const [video, setVideo] = useState(initial);

    usePolling({
        url: route('videos.status', video.id),
        enabled: video.status === 'pending' || video.status === 'processing',
        interval: 5000,
        onData: (data) => setVideo((prev) => ({ ...prev, ...data })),
        stopWhen: (data) => data.status === 'completed' || data.status === 'failed',
    });

    return (
        <AuthenticatedLayout>
            <Head title={t('videos.resultTitle')} />

            <PageHeader title={t('videos.resultTitle')} />

            <div className="max-w-3xl mx-auto">
                <ProcessingStatus status={video.status} errorMessage={video.error_message} className="mb-6" />

                {video.status === 'completed' && video.video_url && (
                    <VideoPlayer src={video.video_url} className="mb-4" />
                )}

                <div className="bg-white rounded-xl border border-gray-200 p-4">
                    <p className="text-sm text-gray-500">{t('videos.garmentLabel')}: <span className="font-medium text-gray-900">{video.garment.name}</span></p>
                    {video.duration_seconds && <p className="text-sm text-gray-500 mt-1">{t('videos.durationLabel')}: {video.duration_seconds}s</p>}
                    <p className="text-sm text-gray-500 mt-1">{t('videos.createdLabel')}: {video.created_at}</p>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
