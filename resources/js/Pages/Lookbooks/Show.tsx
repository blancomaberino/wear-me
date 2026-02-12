import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Head, Link, router } from '@inertiajs/react';
import { Lookbook, LookbookItem } from '@/types';
import { BookOpen, Trash2, ArrowLeft, Wand2, Sparkles } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    lookbook: Lookbook;
}

export default function Show({ lookbook }: Props) {
    const { t } = useTranslation();
    const items = lookbook.items || [];

    const handleRemoveItem = (itemId: number) => {
        router.delete(route('lookbooks.items.remove', { lookbook: lookbook.id, item: itemId }));
    };

    const handleDelete = () => {
        if (confirm(t('lookbooks.confirmDelete'))) {
            router.delete(route('lookbooks.destroy', lookbook.id));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={lookbook.name} />

            <PageHeader
                title={lookbook.name}
                description={lookbook.description || undefined}
                actions={
                    <div className="flex gap-2">
                        <Button variant="ghost" onClick={() => router.visit(route('lookbooks.index'))}>
                            <ArrowLeft className="h-4 w-4" /> {t('common.back')}
                        </Button>
                        <Button variant="outline" size="sm" onClick={handleDelete}>
                            <Trash2 className="h-4 w-4" /> {t('lookbooks.deleteLookbook')}
                        </Button>
                    </div>
                }
            />

            {items.length === 0 ? (
                <EmptyState
                    icon={BookOpen}
                    title={t('lookbooks.emptyItems')}
                    description={t('lookbooks.emptyItemsDesc')}
                    action={
                        <div className="flex flex-col sm:flex-row gap-3">
                            <Link href={route('tryon.history')}>
                                <Button variant="outline">
                                    <Wand2 className="h-4 w-4" /> {t('lookbooks.goToTryOns')}
                                </Button>
                            </Link>
                            <Link href={route('outfits.index')}>
                                <Button variant="outline">
                                    <Sparkles className="h-4 w-4" /> {t('lookbooks.goToOutfits')}
                                </Button>
                            </Link>
                        </div>
                    }
                />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {items.map((item) => (
                        <Card key={item.id}>
                            <CardBody>
                                {/* TryOnResult items */}
                                {item.item && 'result_url' in item.item && item.item.result_url && (
                                    <img src={item.item.result_url} alt="" className="w-full h-48 object-cover rounded-lg mb-3 bg-surface-50" />
                                )}
                                {/* OutfitSuggestion items */}
                                {item.item && 'suggestion_text' in item.item && (
                                    <div className="mb-3">
                                        {'garments' in item.item && item.item.garments && (
                                            <div className="flex gap-2 mb-2">
                                                {(item.item.garments as any[]).map((g: any) => (
                                                    <img key={g.id} src={g.thumbnail_url || ''} alt={g.name || ''} className="h-14 w-14 rounded-lg object-cover bg-surface-50" loading="lazy" />
                                                ))}
                                            </div>
                                        )}
                                        <p className="text-body-sm text-surface-700">{(item.item as any).suggestion_text}</p>
                                        {'occasion' in item.item && item.item.occasion && (
                                            <Badge variant="brand" size="sm" className="mt-1">{(item.item as any).occasion}</Badge>
                                        )}
                                    </div>
                                )}
                                {item.note && <p className="text-caption text-surface-500 mb-2">{item.note}</p>}
                                <Button variant="ghost" size="sm" onClick={() => handleRemoveItem(item.id)}>
                                    <Trash2 className="h-3.5 w-3.5" /> {t('lookbooks.removeItem')}
                                </Button>
                            </CardBody>
                        </Card>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
