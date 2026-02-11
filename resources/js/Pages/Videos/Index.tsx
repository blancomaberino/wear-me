import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Video, ArrowRight } from 'lucide-react';
import { useState } from 'react';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Button } from '@/Components/ui/Button';
import { useTranslation } from 'react-i18next';

interface TryOnResultOption {
    id: number;
    result_url: string | null;
    model_image: { thumbnail_url: string | null };
    garment: { name: string; thumbnail_url: string | null };
}

interface Props {
    tryOnResults: TryOnResultOption[];
}

export default function Index({ tryOnResults }: Props) {
    const { t } = useTranslation();
    const [selectedResult, setSelectedResult] = useState<number | null>(null);
    const [processing, setProcessing] = useState(false);

    const handleGenerate = () => {
        if (!selectedResult) return;
        setProcessing(true);
        router.post(route('videos.store'), { tryon_result_id: selectedResult }, { onFinish: () => setProcessing(false) });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('videos.generateTitle')} />

            <PageHeader title={t('videos.generateTitle')} />

            {tryOnResults.length === 0 ? (
                <div className="text-center py-16 text-gray-500">
                    <Video className="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p className="text-lg">{t('videos.emptyState')}</p>
                    <p className="text-sm mt-1">{t('videos.emptyStateHint')}</p>
                    <Link href={route('tryon.index')} className="mt-4 inline-block text-brand-600 hover:text-brand-700 font-medium">{t('videos.goToTryOn')}</Link>
                </div>
            ) : (
                <>
                    <h3 className="text-lg font-semibold text-gray-900 mb-3">{t('videos.selectPrompt')}</h3>
                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                        {tryOnResults.map((r) => (
                            <div
                                key={r.id}
                                onClick={() => setSelectedResult(r.id)}
                                className={`cursor-pointer rounded-xl overflow-hidden border transition-all ${selectedResult === r.id ? 'ring-4 ring-brand-500 border-brand-500' : 'border-gray-200 hover:shadow-md'}`}
                            >
                                <div className="aspect-square bg-gray-100">
                                    {r.result_url && <img src={r.result_url} alt="" className="w-full h-full object-cover" loading="lazy" />}
                                </div>
                                <div className="p-2 bg-white">
                                    <p className="text-sm font-medium text-gray-900 truncate">{r.garment.name}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                    <div className="text-center">
                        <Button
                            size="lg"
                            onClick={handleGenerate}
                            disabled={!selectedResult}
                            loading={processing}
                        >
                            <Video className="w-5 h-5" /> {processing ? t('videos.processing') : t('videos.generateButton')}
                        </Button>
                    </div>
                </>
            )}
        </AuthenticatedLayout>
    );
}
