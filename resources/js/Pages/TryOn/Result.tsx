import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import ProcessingStatus from '@/Components/ProcessingStatus';
import { Button } from '@/Components/ui/Button';
import { Card, CardBody } from '@/Components/ui/Card';
import { Head, Link, router } from '@inertiajs/react';
import { TryOnResult, Lookbook } from '@/types';
import { usePolling } from '@/hooks/usePolling';
import { useState } from 'react';
import { Heart, Download, Share2, Layers, Wand2, BookOpen } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AddToLookbookDialog from '@/Components/AddToLookbookDialog';

interface Props {
    tryOnResult: TryOnResult;
    lookbooks: Lookbook[];
}

export default function Result({ tryOnResult: initial, lookbooks }: Props) {
    const { t } = useTranslation();
    const [result, setResult] = useState(initial);
    const [copied, setCopied] = useState(false);
    const [showAddToLookbook, setShowAddToLookbook] = useState(false);

    usePolling({
        url: route('tryon.status', result.id),
        enabled: result.status === 'pending' || result.status === 'processing',
        interval: 5000,
        onData: (data) => setResult((prev) => ({ ...prev, ...data })),
        stopWhen: (data) => data.status === 'completed' || data.status === 'failed',
    });

    const handleDownload = async () => {
        if (!result.result_url) return;
        const response = await fetch(result.result_url);
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `wearme-tryon-${result.id}.jpg`;
        a.click();
        URL.revokeObjectURL(url);
    };

    const handleShare = () => {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('tryon.resultTitle')} />
            <PageHeader title={t('tryon.resultTitle')} />

            <div className="max-w-4xl mx-auto">
                <ProcessingStatus status={result.status} errorMessage={result.error_message} className="mb-6" />

                {/* Processing skeleton */}
                {(result.status === 'pending' || result.status === 'processing') && (
                    <div className="rounded-card bg-gradient-to-br from-brand-50 via-surface-50 to-brand-50 p-16 flex flex-col items-center justify-center animate-pulse">
                        <div className="h-12 w-12 border-3 border-brand-200 border-t-brand-600 rounded-full animate-spin mb-4" />
                        <p className="text-body text-surface-600 font-medium">{t('tryon.generating')}</p>
                        <p className="text-body-sm text-surface-400 mt-1">{t('tryon.generatingHint')}</p>
                    </div>
                )}

                {/* Result image */}
                {result.status === 'completed' && result.result_url && (
                    <Card className="overflow-hidden animate-fade-in">
                        <img src={result.result_url} alt={t('tryon.tryOnResult')} className="w-full" />
                        <CardBody>
                            <div className="flex flex-wrap items-center gap-3">
                                <Button
                                    variant={result.is_favorite ? 'primary' : 'outline'}
                                    size="sm"
                                    onClick={() => router.patch(route('tryon.favorite', result.id))}
                                >
                                    <Heart className={`h-4 w-4 ${result.is_favorite ? 'fill-current' : ''}`} />
                                    {result.is_favorite ? t('tryon.favorited') : t('tryon.favorite')}
                                </Button>
                                <Button variant="outline" size="sm" onClick={handleDownload}>
                                    <Download className="h-4 w-4" /> {t('tryon.download')}
                                </Button>
                                <Button variant="outline" size="sm" onClick={handleShare}>
                                    <Share2 className="h-4 w-4" /> {copied ? t('tryon.copied') : t('tryon.share')}
                                </Button>
                                <Button variant="outline" size="sm" onClick={() => setShowAddToLookbook(true)}>
                                    <BookOpen className="h-4 w-4" /> {t('lookbooks.addItem')}
                                </Button>
                                <Link href={route('tryon.index', { source_result: result.id })}>
                                    <Button variant="outline" size="sm">
                                        <Layers className="h-4 w-4" /> {t('tryon.tryOnMore')}
                                    </Button>
                                </Link>
                                <span className="ml-auto text-caption text-surface-400">{result.created_at}</span>
                            </div>
                        </CardBody>
                    </Card>
                )}

                {/* Source images */}
                <div className="mt-6">
                    <h3 className="text-heading-sm text-surface-700 mb-3">{t('tryon.sourceImages')}</h3>
                    <div className={`grid gap-4 ${(result.garments?.length ?? 1) > 1 ? 'grid-cols-2 md:grid-cols-3' : 'grid-cols-2'}`}>
                        <Card>
                            <CardBody>
                                <p className="text-caption font-medium text-surface-500 mb-2">{t('tryon.yourPhoto')}</p>
                                {result.model_image.url && <img src={result.model_image.url} alt="Model" className="rounded-lg w-full" />}
                            </CardBody>
                        </Card>
                        {result.garments && result.garments.length > 0 ? (
                            result.garments.map((g) => (
                                <Card key={g.id}>
                                    <CardBody>
                                        <p className="text-caption font-medium text-surface-500 mb-2">{g.name}</p>
                                        {g.url && <img src={g.url} alt={g.name} className="rounded-lg w-full" />}
                                    </CardBody>
                                </Card>
                            ))
                        ) : result.garment && (
                            <Card>
                                <CardBody>
                                    <p className="text-caption font-medium text-surface-500 mb-2">{result.garment.name}</p>
                                    {result.garment.url && <img src={result.garment.url} alt="Garment" className="rounded-lg w-full" />}
                                </CardBody>
                            </Card>
                        )}
                    </div>
                </div>

                {/* Try another */}
                {result.status === 'completed' && (
                    <div className="mt-8 text-center">
                        <Link href={route('tryon.index')}>
                            <Button variant="secondary" size="lg">
                                <Wand2 className="h-5 w-5" /> {t('tryon.tryAnother')}
                            </Button>
                        </Link>
                    </div>
                )}
            </div>

            <AddToLookbookDialog
                open={showAddToLookbook}
                onClose={() => setShowAddToLookbook(false)}
                lookbooks={lookbooks}
                itemableType="tryon_result"
                itemableId={result.id}
            />
        </AuthenticatedLayout>
    );
}
