import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { EmptyState } from '@/Components/ui/EmptyState';
import { Dialog } from '@/Components/ui/Dialog';
import { Input } from '@/Components/ui/Input';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Lookbook, LookbookItem } from '@/types';
import { BookOpen, Trash2, ArrowLeft, Wand2, Sparkles, Pencil, ImageIcon } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useState, FormEventHandler } from 'react';

interface Props {
    lookbook: Lookbook;
}

export default function Show({ lookbook }: Props) {
    const { t } = useTranslation();
    const items = lookbook.items || [];
    const [showEdit, setShowEdit] = useState(false);

    const { data, setData, put, processing, reset, errors } = useForm({
        name: lookbook.name,
        description: lookbook.description || '',
        is_public: lookbook.is_public || false,
    });

    const handleRemoveItem = (itemId: number) => {
        router.delete(route('lookbooks.items.remove', { lookbook: lookbook.id, item: itemId }));
    };

    const handleDelete = () => {
        if (confirm(t('lookbooks.confirmDelete'))) {
            router.delete(route('lookbooks.destroy', lookbook.id));
        }
    };

    const handleSetCover = (itemId: number) => {
        router.put(route('lookbooks.update', lookbook.id), { cover_item_id: itemId });
    };

    const submitEdit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('lookbooks.update', lookbook.id), {
            onSuccess: () => { setShowEdit(false); },
        });
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
                        <Button variant="outline" size="sm" onClick={() => setShowEdit(true)}>
                            <Pencil className="h-4 w-4" /> {t('lookbooks.editDetails')}
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
                    {items.map((item) => {
                        const isCover = item.item && 'result_url' in item.item && item.item.result_url === lookbook.cover_image_url;
                        const canSetCover = item.item && 'result_url' in item.item && item.item.result_url;

                        return (
                            <Card key={item.id}>
                                <CardBody>
                                    {/* TryOnResult items */}
                                    {item.item && 'result_url' in item.item && item.item.result_url && (
                                        <div className="relative">
                                            <img src={item.item.result_url} alt="" className="w-full h-48 object-cover rounded-lg mb-3 bg-surface-50" />
                                            {isCover && (
                                                <Badge variant="brand" size="sm" className="absolute top-2 right-2">
                                                    {t('lookbooks.currentCover')}
                                                </Badge>
                                            )}
                                        </div>
                                    )}
                                    {/* OutfitSuggestion items */}
                                    {item.item && 'suggestion_text' in item.item && (
                                        <div className="mb-3">
                                            {'garments' in item.item && item.item.garments && (
                                                <div className="flex gap-2 mb-2">
                                                    {(item.item.garments as any[]).map((g: any) => (
                                                        g.thumbnail_url ? (
                                                            <img key={g.id} src={g.thumbnail_url} alt={g.name || ''} className="h-14 w-14 rounded-lg object-cover bg-surface-50" loading="lazy" />
                                                        ) : (
                                                            <div key={g.id} className="h-14 w-14 rounded-lg bg-surface-100" />
                                                        )
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
                                    <div className="flex gap-2">
                                        {canSetCover && !isCover && (
                                            <Button variant="outline" size="sm" onClick={() => handleSetCover(item.id)}>
                                                <ImageIcon className="h-3.5 w-3.5" /> {t('lookbooks.setCover')}
                                            </Button>
                                        )}
                                        <Button variant="ghost" size="sm" onClick={() => handleRemoveItem(item.id)}>
                                            <Trash2 className="h-3.5 w-3.5" /> {t('lookbooks.removeItem')}
                                        </Button>
                                    </div>
                                </CardBody>
                            </Card>
                        );
                    })}
                </div>
            )}

            <Dialog open={showEdit} onClose={() => { reset(); setShowEdit(false); }} title={t('lookbooks.editLookbook')} size="md">
                <form onSubmit={submitEdit} className="space-y-4">
                    <Input label={t('lookbooks.name')} value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder={t('lookbooks.namePlaceholder')} error={errors.name} />
                    <div>
                        <label className="block text-body-sm font-medium text-surface-700 mb-1">{t('lookbooks.description')}</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder={t('lookbooks.descPlaceholder')}
                            className="w-full rounded-input border border-surface-300 px-3 py-2 text-body-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                            rows={3}
                        />
                    </div>
                    <label className="flex items-center gap-2">
                        <input type="checkbox" checked={data.is_public} onChange={(e) => setData('is_public', e.target.checked)} className="rounded border-surface-300 text-brand-600 focus:ring-brand-500" />
                        <span className="text-body-sm text-surface-700">{t('lookbooks.makePublic')}</span>
                    </label>
                    <div className="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="ghost" onClick={() => { reset(); setShowEdit(false); }}>{t('common.cancel')}</Button>
                        <Button type="submit" loading={processing}>{t('common.save')}</Button>
                    </div>
                </form>
            </Dialog>
        </AuthenticatedLayout>
    );
}
