import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Tabs } from '@/Components/ui/Tabs';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Button } from '@/Components/ui/Button';
import { Head, Link, router } from '@inertiajs/react';
import { TryOnResult, PaginatedData } from '@/types';
import { useState } from 'react';
import { Wand2, Heart, Clock } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    results: PaginatedData<TryOnResult>;
}

export default function History({ results }: Props) {
    const { t } = useTranslation();
    const [filter, setFilter] = useState('all');

    const filtered = filter === 'all'
        ? results.data
        : filter === 'favorites'
            ? results.data.filter((r) => r.is_favorite)
            : results.data;

    const tabs = [
        { id: 'all', label: t('tryon.allTab'), count: results.data.length },
        { id: 'favorites', label: t('tryon.favoritesTab'), count: results.data.filter((r) => r.is_favorite).length },
    ];

    return (
        <AuthenticatedLayout>
            <Head title={t('tryon.historyTitle')} />

            <PageHeader
                title={t('tryon.historyTitle')}
                description={t('tryon.totalResults', { count: results.meta.total })}
                actions={
                    <Link href={route('tryon.index')}>
                        <Button><Wand2 className="h-4 w-4" /> {t('tryon.newTryOn')}</Button>
                    </Link>
                }
            />

            <Tabs tabs={tabs} activeTab={filter} onChange={setFilter} variant="pill" className="mb-6" />

            {filtered.length === 0 ? (
                <EmptyState
                    icon={Clock}
                    title={filter === 'favorites' ? t('tryon.noFavorites') : t('tryon.noHistory')}
                    description={filter === 'favorites' ? t('tryon.noFavoritesDesc') : t('tryon.noHistoryDesc')}
                    action={
                        <Link href={route('tryon.index')}>
                            <Button variant="outline"><Wand2 className="h-4 w-4" /> {t('tryon.startTryOn')}</Button>
                        </Link>
                    }
                />
            ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    {filtered.map((result) => (
                        <Link key={result.id} href={route('tryon.show', result.id)}>
                            <Card variant="interactive" className="overflow-hidden">
                                <div className="relative aspect-square bg-surface-50">
                                    {result.result_url ? (
                                        <img src={result.result_url} alt="Try-on" className="w-full h-full object-cover" loading="lazy" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center">
                                            <Badge variant={result.status === 'failed' ? 'danger' : 'brand'}>{result.status}</Badge>
                                        </div>
                                    )}
                                    {result.is_favorite && (
                                        <div className="absolute top-2 right-2 p-1 bg-white/80 rounded-full">
                                            <Heart className="h-3.5 w-3.5 text-red-500 fill-current" />
                                        </div>
                                    )}
                                </div>
                                <CardBody className="p-3">
                                    <p className="text-body-sm font-medium text-surface-900 truncate">
                                        {result.garment?.name || t('tryon.tryOnResult')}
                                    </p>
                                    <p className="text-caption text-surface-400">{result.created_at}</p>
                                </CardBody>
                            </Card>
                        </Link>
                    ))}
                </div>
            )}

            {/* Pagination */}
            {(results.links.prev || results.links.next) && (
                <div className="flex items-center justify-center gap-3 mt-8">
                    {results.links.prev && (
                        <Button variant="outline" size="sm" onClick={() => router.get(results.links.prev!)}>{t('common.previous')}</Button>
                    )}
                    <span className="text-body-sm text-surface-500">
                        {t('tryon.page', { current: results.meta.current_page, last: results.meta.last_page })}
                    </span>
                    {results.links.next && (
                        <Button variant="outline" size="sm" onClick={() => router.get(results.links.next!)}>{t('common.next')}</Button>
                    )}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
