import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageHeader } from '@/Components/layout/PageHeader';
import { Card, CardBody } from '@/Components/ui/Card';
import { Button } from '@/Components/ui/Button';
import { Badge } from '@/Components/ui/Badge';
import { HarmonyBadge } from '@/Components/HarmonyBadge';
import { Head, Link, router } from '@inertiajs/react';
import { Outfit, Garment } from '@/types';
import { ArrowLeft, Trash2, Wand2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';

interface Props {
    outfit: Outfit;
    garments: Garment[];
}

export default function ShowOutfit({ outfit }: Props) {
    const { t } = useTranslation();

    const handleDelete = () => {
        if (confirm(t('outfits.deleteOutfit'))) {
            router.delete(route('my-outfits.destroy', outfit.id));
        }
    };

    const garmentIds = outfit.garments?.map((g) => g.id).join(',') || '';

    return (
        <AuthenticatedLayout>
            <Head title={outfit.name} />

            <PageHeader
                title={outfit.name}
                description={t('outfits.outfitDetails')}
                actions={
                    <div className="flex gap-2">
                        <Link href={route('my-outfits.index')}>
                            <Button variant="ghost">
                                <ArrowLeft className="h-4 w-4" /> {t('common.back')}
                            </Button>
                        </Link>
                        <Button variant="outline" size="sm" onClick={handleDelete}>
                            <Trash2 className="h-4 w-4" />
                        </Button>
                    </div>
                }
            />

            {/* Sub-navigation tabs */}
            <div className="flex gap-2 mb-4">
                <Link href={route('outfits.index')}>
                    <Button variant="ghost" size="sm">{t('outfits.suggestionsTab')}</Button>
                </Link>
                <Link href={route('outfits.templates')}>
                    <Button variant="ghost" size="sm">{t('outfits.templatesTab')}</Button>
                </Link>
                <Link href={route('my-outfits.index')}>
                    <Button variant="primary" size="sm">{t('outfits.myOutfitsTab')}</Button>
                </Link>
                <Link href={route('outfits.saved')}>
                    <Button variant="ghost" size="sm">{t('outfits.savedTab')}</Button>
                </Link>
            </div>

            {/* Outfit Info Card */}
            <Card className="mb-6">
                <CardBody>
                    <div className="flex items-start justify-between mb-4">
                        <div className="flex gap-2 items-center">
                            {outfit.occasion && (
                                <Badge variant="brand">{outfit.occasion}</Badge>
                            )}
                            {outfit.harmony_score != null && (
                                <HarmonyBadge
                                    colors={outfit.garments?.flatMap((g) => g.color_tags || []) || []}
                                    score={outfit.harmony_score}
                                />
                            )}
                        </div>
                    </div>

                    {outfit.template && (
                        <div className="mb-3">
                            <span className="text-caption text-surface-500">{t('outfits.templateLabel')}: </span>
                            <span className="text-body-sm text-surface-900">{outfit.template.name}</span>
                        </div>
                    )}

                    {outfit.notes && (
                        <div className="mb-3">
                            <span className="text-caption text-surface-500">{t('outfits.notesLabel')}: </span>
                            <p className="text-body-sm text-surface-700 mt-1">{outfit.notes}</p>
                        </div>
                    )}

                    <div className="text-caption text-surface-400">
                        {outfit.created_at}
                    </div>
                </CardBody>
            </Card>

            {/* Garments Grid */}
            <h2 className="text-heading-sm text-surface-900 mb-4">{t('common.garments')}</h2>
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                {outfit.garments?.map((garment) => (
                    <Card key={garment.id}>
                        <CardBody>
                            <img
                                src={garment.thumbnail_url || garment.url}
                                alt={garment.name || ''}
                                className="w-full h-32 object-cover rounded-lg bg-surface-50 mb-2"
                                loading="lazy"
                            />
                            <p className="text-body-sm text-surface-900 truncate mb-1">
                                {garment.name || t('common.garment')}
                            </p>
                            <Badge variant="neutral" size="sm">
                                {garment.category === 'upper'
                                    ? t('outfits.catTop')
                                    : garment.category === 'lower'
                                    ? t('outfits.catBottom')
                                    : t('outfits.catDress')}
                            </Badge>
                        </CardBody>
                    </Card>
                ))}
            </div>

            {/* Try On Button */}
            {garmentIds && (
                <Link href={`${route('tryon.index')}?garment_ids=${garmentIds}`}>
                    <Button variant="primary" size="lg">
                        <Wand2 className="h-4 w-4" /> {t('outfits.tryOn')}
                    </Button>
                </Link>
            )}
        </AuthenticatedLayout>
    );
}
