import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { TryOnVideo, PaginatedData } from '@/types';
import { Video, Play } from 'lucide-react';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/Badge';
import { Card, CardBody } from '@/Components/ui/Card';
import { useTranslation } from 'react-i18next';

interface Props {
    videos: PaginatedData<TryOnVideo>;
}

export default function History({ videos }: Props) {
    const { t } = useTranslation();

    return (
        <AuthenticatedLayout>
            <Head title={t('videos.historyTitle')} />

            <PageHeader title={t('videos.historyTitle')} />

            <Card className="mb-6 bg-amber-50 border-amber-200">
                <CardBody>
                    <div className="flex items-center gap-3">
                        <Badge variant="warning">{t('videos.notice')}</Badge>
                        <p className="text-body-sm text-surface-700">{t('videos.unavailableNotice')}</p>
                    </div>
                </CardBody>
            </Card>

            {videos.data.length === 0 ? (
                <div className="text-center py-16 text-gray-500">
                    <Video className="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p className="text-lg">{t('videos.emptyHistory')}</p>
                    <Link href={route('videos.index')} className="mt-4 inline-block text-brand-600 hover:text-brand-700 font-medium">{t('videos.generateFirst')}</Link>
                </div>
            ) : (
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    {videos.data.map((v) => (
                        <Link key={v.id} href={route('videos.show', v.id)} className="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                            <div className="relative aspect-video bg-gray-900 flex items-center justify-center">
                                {v.garment.thumbnail_url && <img src={v.garment.thumbnail_url} alt="" className="w-full h-full object-cover opacity-60" />}
                                <Play className="absolute w-10 h-10 text-white/80 group-hover:text-white transition" />
                                <div className={`absolute top-2 right-2 text-xs font-medium px-2 py-0.5 rounded-full ${v.status === 'completed' ? 'bg-green-100 text-green-700' : v.status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}`}>
                                    {t(`processing.status${v.status.charAt(0).toUpperCase()}${v.status.slice(1)}` as any)}
                                </div>
                            </div>
                            <div className="p-3">
                                <p className="text-sm font-medium text-gray-900 truncate">{v.garment.name}</p>
                                <p className="text-xs text-gray-500">{v.created_at}{v.duration_seconds ? ` · ${v.duration_seconds}s` : ''}</p>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
