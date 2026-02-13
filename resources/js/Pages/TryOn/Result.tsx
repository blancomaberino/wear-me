import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import ProcessingStatus from '@/Components/ProcessingStatus';
import { Button } from '@/Components/ui/Button';
import { Card, CardBody } from '@/Components/ui/Card';
import { Head, Link, router } from '@inertiajs/react';
import { TryOnResult, Lookbook } from '@/types';
import { usePolling } from '@/hooks/usePolling';
import { useState, useEffect } from 'react';
import { Heart, Download, Share2, Layers, Wand2, BookOpen, ZoomIn } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AddToLookbookDialog from '@/Components/AddToLookbookDialog';
import ImageLightbox from '@/Components/ImageLightbox';

interface Props {
    tryOnResult: TryOnResult;
    lookbooks: Lookbook[];
}

export default function Result({ tryOnResult: initial, lookbooks }: Props) {
    const { t } = useTranslation();
    const [result, setResult] = useState(initial);
    useEffect(() => { setResult(initial); }, [initial]);
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

            <div className="max-w-5xl mx-auto">
                {result.status !== 'completed' && (
                    <ProcessingStatus status={result.status} errorMessage={result.error_message} className="mb-6" />
                )}

                {/* Processing skeleton */}
                {(result.status === 'pending' || result.status === 'processing') && (
                    <div className="rounded-card bg-gradient-to-br from-brand-50 via-surface-50 to-brand-50 p-16 flex flex-col items-center justify-center animate-pulse">
                        <div className="h-12 w-12 border-3 border-brand-200 border-t-brand-600 rounded-full animate-spin mb-4" />
                        <p className="text-body text-surface-600 font-medium">{t('tryon.generating')}</p>
                        <p className="text-body-sm text-surface-400 mt-1">{t('tryon.generatingHint')}</p>
                    </div>
                )}

                {/* Completed result — side-by-side layout */}
                {result.status === 'completed' && result.result_url && (
                    <div className="flex flex-col md:flex-row gap-6 animate-fade-in">
                        {/* Result image — hero */}
                        <div className="flex-1 min-w-0">
                            <ImageLightbox src={result.result_url!} alt={t('tryon.tryOnResult')}>
                                {(open) => (
                                    <Card className="overflow-hidden cursor-zoom-in group" onClick={open}>
                                        <div className="bg-surface-50 flex items-center justify-center relative">
                                            <img
                                                src={result.result_url!}
                                                alt={t('tryon.tryOnResult')}
                                                className="w-full max-h-[70vh] object-contain"
                                            />
                                            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors flex items-center justify-center">
                                                <div className="opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-full p-2">
                                                    <ZoomIn className="h-5 w-5 text-white" />
                                                </div>
                                            </div>
                                        </div>
                                    </Card>
                                )}
                            </ImageLightbox>
                        </div>

                        {/* Sidebar — actions + source images */}
                        <div className="w-full md:w-72 lg:w-80 flex-shrink-0 space-y-5">
                            {/* Actions */}
                            <Card>
                                <CardBody className="space-y-2">
                                    <div className="grid grid-cols-2 gap-2">
                                        <Button
                                            variant={result.is_favorite ? 'primary' : 'outline'}
                                            size="sm"
                                            className="w-full"
                                            onClick={() => router.patch(route('tryon.favorite', result.id))}
                                        >
                                            <Heart className={`h-4 w-4 flex-shrink-0 ${result.is_favorite ? 'fill-current' : ''}`} />
                                            <span className="truncate">{result.is_favorite ? t('tryon.favorited') : t('tryon.favorite')}</span>
                                        </Button>
                                        <Button variant="outline" size="sm" className="w-full" onClick={handleDownload}>
                                            <Download className="h-4 w-4 flex-shrink-0" /> <span className="truncate">{t('tryon.download')}</span>
                                        </Button>
                                        <Button variant="outline" size="sm" className="w-full" onClick={handleShare}>
                                            <Share2 className="h-4 w-4 flex-shrink-0" /> <span className="truncate">{copied ? t('tryon.copied') : t('tryon.share')}</span>
                                        </Button>
                                    </div>
                                    <Button variant="outline" size="sm" className="w-full" onClick={() => setShowAddToLookbook(true)}>
                                        <BookOpen className="h-4 w-4 flex-shrink-0" /> {t('lookbooks.addItem')}
                                    </Button>
                                    <Link href={route('tryon.index', { source_result: result.id })} className="block">
                                        <Button variant="outline" size="sm" className="w-full">
                                            <Layers className="h-4 w-4 flex-shrink-0" /> {t('tryon.tryOnMore')}
                                        </Button>
                                    </Link>
                                    <p className="text-caption text-surface-400 text-center pt-1">{result.created_at}</p>
                                </CardBody>
                            </Card>

                            {/* Source images — compact thumbnails */}
                            <div>
                                <h3 className="text-caption font-semibold text-surface-500 uppercase tracking-wider mb-3">{t('tryon.sourceImages')}</h3>
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        {result.model_image.url ? (
                                            <ImageLightbox src={result.model_image.url} alt="Model">
                                                {(open) => (
                                                    <div className="rounded-lg overflow-hidden bg-surface-50 aspect-[3/4] cursor-zoom-in" onClick={open}>
                                                        <img src={result.model_image.url} alt="Model" className="w-full h-full object-cover" />
                                                    </div>
                                                )}
                                            </ImageLightbox>
                                        ) : (
                                            <div className="rounded-lg overflow-hidden bg-surface-50 aspect-[3/4]" />
                                        )}
                                        <p className="text-caption text-surface-500 mt-1.5 truncate">{t('tryon.yourPhoto')}</p>
                                    </div>
                                    {result.garments && result.garments.length > 0 ? (
                                        result.garments.map((g) => (
                                            <div key={g.id}>
                                                {g.url ? (
                                                    <ImageLightbox src={g.url} alt={g.name}>
                                                        {(open) => (
                                                            <div className="rounded-lg overflow-hidden bg-surface-50 aspect-[3/4] cursor-zoom-in" onClick={open}>
                                                                <img src={g.url} alt={g.name} className="w-full h-full object-cover" />
                                                            </div>
                                                        )}
                                                    </ImageLightbox>
                                                ) : (
                                                    <div className="rounded-lg overflow-hidden bg-surface-50 aspect-[3/4]" />
                                                )}
                                                <p className="text-caption text-surface-500 mt-1.5 truncate">{g.name}</p>
                                            </div>
                                        ))
                                    ) : result.garment && (
                                        <div>
                                            {result.garment!.url ? (
                                                <ImageLightbox src={result.garment!.url} alt="Garment">
                                                    {(open) => (
                                                        <div className="rounded-lg overflow-hidden bg-surface-50 aspect-[3/4] cursor-zoom-in" onClick={open}>
                                                            <img src={result.garment!.url!} alt="Garment" className="w-full h-full object-cover" />
                                                        </div>
                                                    )}
                                                </ImageLightbox>
                                            ) : (
                                                <div className="rounded-lg overflow-hidden bg-surface-50 aspect-[3/4]" />
                                            )}
                                            <p className="text-caption text-surface-500 mt-1.5 truncate">{result.garment.name}</p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Try another */}
                            <Link href={route('tryon.index')} className="block">
                                <Button variant="secondary" size="lg" className="w-full">
                                    <Wand2 className="h-5 w-5" /> {t('tryon.tryAnother')}
                                </Button>
                            </Link>
                        </div>
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
